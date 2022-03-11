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

use App\Domain\Work\Entity\Work;
use App\Application\Constant\{
    ConversationTypeConstant,
    WorkStatusConstant,
    ConversationMessageStatusTypeConstant
};
use App\Application\Helper\ConversationHelper;
use App\Application\Service\EntityManagerService;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\EventDispatcher\ConversationEventDispatcherService;
use App\Domain\Conversation\Factory\ConversationFactory;
use App\Domain\Conversation\Repository\ConversationRepository;
use App\Domain\Conversation\Service\{
    ConversationVariationService
};
use App\Domain\Conversation\Service\ConversationStatusService;
use App\Domain\ConversationMessage\ConversationComposeMessageModel;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\User\Entity\User;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;

class ConversationFacade
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private ConversationMessageFacade $conversationMessageFacade,
        private ConversationStatusService $conversationStatusService,
        private ConversationVariationService $conversationVariationService,
        private ConversationEventDispatcherService $conversationEventDispatcherService,
        private ConversationFactory $conversationFactory,
        private ConversationRepository $conversationRepository
    ) {
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
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE);

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
                $conversationType = $this->entityManagerService->getReference(ConversationType::class, ConversationTypeConstant::WORK);

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

            $this->entityManagerService->clear();

            $message = $this->conversationMessageFacade
                ->find($conversationMessage->getId());

            $this->conversationEventDispatcherService
                ->onConversationMessageCreate($message);
        } else {
            if (is_array($modelConversation)) {
                /** @var Conversation $modelConversation */
                $modelConversation = $modelConversation[0];
            }

            /** @var Conversation $conversation */
            foreach ($conversations as $conversation) {
                if ($modelConversation->getWork() === $conversation->getWork() &&
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

                $this->entityManagerService->clear();
            }
        }
    }
}
