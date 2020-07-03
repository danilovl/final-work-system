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

namespace App\Model\Conversation;

use Doctrine\Common\Collections\ArrayCollection;
use App\Model\ConversationMessage\ConversationComposeMessageModel;
use App\EventDispatcher\ConversationEventDispatcherService;
use App\Services\{
    ConversationStatusService,
    ConversationVariationService,
    EntityManagerService
};
use Doctrine\ORM\Query;
use App\Constant\{
    WorkStatusConstant,
    ConversationTypeConstant,
    ConversationMessageStatusTypeConstant
};
use App\Repository\ConversationRepository;
use App\Entity\{
    Work,
    Conversation,
    ConversationType,
    ConversationParticipant,
    WorkStatus
};
use App\Entity\User;

class ConversationFacade
{
    private EntityManagerService $em;
    private ConversationRepository $conversationRepository;
    private ConversationStatusService $conversationStatusService;
    private ConversationVariationService $conversationVariationService;
    private ConversationMessageFacade $conversationMessageFacade;
    private ConversationEventDispatcherService $conversationEventDispatcherService;
    private ConversationFactory $conversationFactory;

    public function __construct(
        EntityManagerService $entityManager,
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

    public function queryConversationsByUser(User $user): Query
    {
        return $this->conversationRepository
            ->allByUser($user)
            ->getQuery();
    }

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
