<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace FinalWork\FinalWorkBundle\Model\Conversation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\MappingException;
use FinalWork\FinalWorkBundle\Model\ConversationMessage\ConversationComposeMessageModel;
use FinalWork\FinalWorkBundle\Services\EventDispatcher\ConversationEventDispatcherService;
use FinalWork\FinalWorkBundle\Services\{
    ConversationStatusService,
    ConversationVariationService
};
use Doctrine\ORM\{
    Query,
    ORMException,
    EntityManager,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Constant\{
    WorkStatusConstant,
    ConversationTypeConstant,
    ConversationMessageStatusTypeConstant
};
use FinalWork\FinalWorkBundle\Entity\Repository\ConversationRepository;
use FinalWork\FinalWorkBundle\Entity\{Work,
    Conversation,
    ConversationType,
    ConversationParticipant,
    WorkStatus
};
use FinalWork\SonataUserBundle\Entity\User;

class ConversationFacade
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ConversationRepository
     */
    private $conversationRepository;

    /**
     * @var ConversationStatusService
     */
    private $conversationStatusService;

    /**
     * @var ConversationVariationService
     */
    private $conversationVariationService;

    /**
     * @var ConversationMessageFacade
     */
    private $conversationMessageFacade;

    /**
     * @var ConversationEventDispatcherService
     */
    private $conversationEventDispatcherService;

    /**
     * @var ConversationFactory
     */
    private $conversationFactory;

    /**
     * ConversationFacade constructor.
     * @param EntityManager $entityManager
     * @param ConversationMessageFacade $conversationMessageFacade
     * @param ConversationStatusService $conversationStatusService
     * @param ConversationVariationService $conversationVariationService
     * @param ConversationEventDispatcherService $conversationEventDispatcherService
     * @param ConversationFactory $conversationFactory
     */
    public function __construct(
        EntityManager $entityManager,
        ConversationMessageFacade $conversationMessageFacade,
        ConversationStatusService $conversationStatusService,
        ConversationVariationService $conversationVariationService,
        ConversationEventDispatcherService $conversationEventDispatcherService,
        ConversationFactory $conversationFactory
    ) {
        $this->em = $entityManager;
        $this->conversationRepository = $entityManager->getRepository(Conversation::class);
        $this->conversationStatusService = $conversationStatusService;
        $this->conversationVariationService = $conversationVariationService;
        $this->conversationMessageFacade = $conversationMessageFacade;
        $this->conversationEventDispatcherService = $conversationEventDispatcherService;
        $this->conversationFactory = $conversationFactory;
    }

    /**
     * @param User $user
     * @return Query
     */
    public function queryConversationsByUser(User $user): Query
    {
        return $this->conversationRepository
            ->findAllByUser($user)
            ->getQuery();
    }

    /**
     * @param iterable $conversations
     * @param User $user
     *
     * @throws ORMException
     */
    public function setIsReadToConversations(
        iterable $conversations,
        User $user
    ): void {
        /** @var Conversation $conversation */
        foreach ($conversations as $conversation) {
            $conversation->setRead(
                $this->conversationStatusService
                    ->isConversationRead($conversation, $user)
            );
        }
    }

    /**
     * @param User $user
     * @return array
     * @throws ORMException
     */
    public function getConversationParticipants(User $user): array
    {
        $conversationArray = [];

        $workArray = $this->conversationVariationService->getWorkConversationsByUser(
            $user,
            $this->em->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
        );

        /** @var Work $work */
        foreach ($workArray as $work) {
            $workUsers = $this->conversationVariationService
                ->getConversationsByWorkUser($work, $user);

            /** @var User $workUser */
            foreach ($workUsers as $workUser) {
                if ($workUser->getId() === $user->getId()) {
                    continue;
                }

                $newConversation = new Conversation;
                $participants = new ArrayCollection;

                $conversationParticipant = new ConversationParticipant;
                $conversationParticipant->setUser($workUser);
                $participants->add($conversationParticipant);

                $conversationParticipant = new ConversationParticipant;
                $conversationParticipant->setUser($user);
                $participants->add($conversationParticipant);

                $newConversation->setName($work->getTitle());
                $newConversation->setWork($work);
                $newConversation->setType($this->em->getReference(ConversationType::class, ConversationTypeConstant::WORK));
                $newConversation->setParticipants($participants);

                if (!in_array($newConversation, $conversationArray, true)) {
                    $conversationArray[] = $newConversation;
                }
            }
        }

        return $conversationArray;
    }

    /**
     * @param User $user
     * @param ConversationComposeMessageModel $conversationComposeMessageModel
     * @throws MappingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function processCreateConversation(
        User $user,
        ConversationComposeMessageModel $conversationComposeMessageModel
    ): void {
        /** @var array $conversations */
        $conversations = $this->queryConversationsByUser($user)
            ->getResult();

        /** @var array $modelConversation */
        $modelConversation = $conversationComposeMessageModel->conversation;
        $content = $conversationComposeMessageModel->content;
        $createNewConversation = false;

        if (count($modelConversation) > 1) {
            $name = $conversationComposeMessageModel->name;
            $conversationParticipantArray = [];
            $conversationParticipantArray[] = $user;

            /** @var Conversation $conversation */
            foreach ($modelConversation as $conversation) {
                if (!in_array($conversation->getRecipient(), $conversationParticipantArray, true)) {
                    $conversationParticipantArray[] = $conversation->getRecipient();
                }
            }

            $newConversation = $this->conversationFactory->createConversation(
                $user,
                ConversationTypeConstant::GROUP,
                null,
                $name
            );

            $this->conversationFactory
                ->createConversationParticipant($newConversation, $conversationParticipantArray);

            $conversationMessage = $this->conversationFactory
                ->createConversationMessage($newConversation, $user, $content);

            $this->conversationFactory->createConversationMessageStatus(
                $newConversation,
                $conversationMessage,
                $user,
                $conversationParticipantArray,
                ConversationMessageStatusTypeConstant::UNREAD
            );

            $this->em->clear();

            $message = $this->conversationMessageFacade
                ->find($conversationMessage->getId());

            $this->conversationEventDispatcherService
                ->onConversationMessageCreate($message);
        } else {
            if (is_array($modelConversation)) {
                $modelConversation = $modelConversation[0];
            }

            /** @var Conversation $conversation */
            foreach ($conversations as $conversation) {
                if ($modelConversation->getWork() === $conversation->getWork() &&
                    $modelConversation->getParticipantIds() === $conversation->getParticipantIds()
                ) {
                    $conversationMessage = $this->conversationFactory
                        ->createConversationMessage($conversation, $user, $content);

                    $participants = $conversation->getParticipants();
                    $this->conversationFactory
                        ->createConversationMessageStatus(
                            $conversation,
                            $conversationMessage,
                            $user,
                            $participants,
                            ConversationMessageStatusTypeConstant::UNREAD
                        );
                    $createNewConversation = true;

                    $message = $this->conversationMessageFacade
                        ->find($conversationMessage->getId());

                    $this->conversationEventDispatcherService
                        ->onConversationMessageCreate($message);
                }
            }

            if ($createNewConversation === false) {
                $name = $modelConversation->getName();
                $work = $modelConversation->getWork();
                $participants = $modelConversation->getParticipants();

                $newConversation = $this->conversationFactory->createConversation(
                    $user,
                    ConversationTypeConstant::WORK,
                    $work,
                    $name
                );
                $this->conversationFactory
                    ->createConversationParticipant($newConversation, $participants);

                $conversationMessage = $this->conversationFactory
                    ->createConversationMessage($newConversation, $user, $content);

                $this->conversationFactory->createConversationMessageStatus(
                    $newConversation,
                    $conversationMessage,
                    $user,
                    $participants,
                    ConversationMessageStatusTypeConstant::UNREAD
                );

                $this->em->clear();
            }
        }
    }
}
