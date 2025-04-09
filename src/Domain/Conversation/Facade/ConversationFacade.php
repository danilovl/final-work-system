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

namespace App\Domain\Conversation\Facade;

use App\Application\Service\EntityManagerService;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\EventDispatcher\ConversationEventDispatcherService;
use App\Domain\Conversation\Factory\ConversationFactory;
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\Conversation\Repository\ConversationRepository;
use App\Domain\Conversation\Service\{
    ConversationStatusService,
    ConversationVariationService
};
use App\Domain\ConversationMessage\Model\ConversationComposeMessageModel;
use App\Domain\ConversationMessageStatusType\Constant\ConversationMessageStatusTypeConstant;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Webmozart\Assert\Assert;

readonly class ConversationFacade
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private ConversationMessageFacade $conversationMessageFacade,
        private ConversationStatusService $conversationStatusService,
        private ConversationVariationService $conversationVariationService,
        private ConversationEventDispatcherService $conversationEventDispatcherService,
        private ConversationFactory $conversationFactory,
        private ConversationRepository $conversationRepository
    ) {}

    public function queryConversationsByParticipantUser(User $user): Query
    {
        return $this->conversationRepository
            ->allByParticipantUser($user)
            ->getQuery();
    }

    public function queryConversationsByParticipantUserTypes(User $user, array $types = []): Query
    {
        Assert::allIsInstanceOf($types, ConversationType::class);

        $queryBuilder = $this->conversationRepository
            ->allByParticipantUser($user);

       return $this->conversationRepository
            ->addFilterByTypes($queryBuilder, $types)
            ->getQuery();
    }

    /**
     * @param int[] $ids
     */
    public function queryConversationsByIds(array $ids): Query
    {
        Assert::allInteger($ids);

        return $this->conversationRepository
            ->allByIds($ids)
            ->getQuery();
    }

    /**
     * @param Conversation[] $conversations
     */
    public function setIsReadToConversations(
        iterable $conversations,
        User $user
    ): void {
        Assert::allIsInstanceOf($conversations, Conversation::class);

        /** @var Conversation $conversation */
        foreach ($conversations as $conversation) {
            $isConversationRead = $this->conversationStatusService
                ->isConversationRead($conversation, $user);

            $conversation->setRead($isConversationRead);
        }
    }

    /**
     * @return Conversation[]
     */
    public function getConversationParticipants(User $user): array
    {
        $conversationArray = [];
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

        $workArray = $this->conversationVariationService->getWorkConversationsByUser(
            $user,
            $workStatus
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

                /** @var ConversationType $conversationType */
                $conversationType = $this->entityManagerService->getReference(ConversationType::class, ConversationTypeConstant::WORK->value);

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
                $newConversation->setType($conversationType);
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
        /** @var Conversation[] $conversations */
        $conversations = $this->queryConversationsByParticipantUser($user)
            ->getResult();

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
                ConversationTypeConstant::GROUP->value,
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
                ConversationMessageStatusTypeConstant::UNREAD->value
            );

            $this->entityManagerService->clear();

            $message = $this->conversationMessageFacade
                ->getConversationMessage($conversationMessage->getId());

            $this->conversationEventDispatcherService
                ->onConversationMessageCreate($message);
        } else {
            /** @var Conversation $modelConversation */
            $modelConversation = $modelConversation[0];

            foreach ($conversations as $conversation) {
                if ($modelConversation->getWork()?->getId() === $conversation->getWork()?->getId() &&
                    ConversationHelper::getParticipantIds($modelConversation) === ConversationHelper::getParticipantIds($conversation)
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
                            ConversationMessageStatusTypeConstant::UNREAD->value
                        );
                    $createNewConversation = true;

                    $message = $this->conversationMessageFacade
                        ->getConversationMessage($conversationMessage->getId());

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
                    ConversationTypeConstant::WORK->value,
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
                    ConversationMessageStatusTypeConstant::UNREAD->value
                );

                $this->entityManagerService->clear();
            }
        }
    }
}
