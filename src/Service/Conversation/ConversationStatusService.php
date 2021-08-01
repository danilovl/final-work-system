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

namespace App\Service\Conversation;

use App\DataTransferObject\Repository\ConversationMessageStatusData;
use App\Service\EntityManagerService;
use DateTime;
use App\Entity\{
    User,
    Conversation,
    ConversationMessage,
    ConversationMessageStatus,
    ConversationMessageStatusType
};
use App\Constant\ConversationMessageStatusTypeConstant;
use App\Repository\ConversationMessageStatusRepository;

class ConversationStatusService
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private ConversationMessageStatusRepository $conversationMessageStatusRepository
    ) {
    }

    public function isConversationRead(
        Conversation $conversation,
        User $user
    ): bool {
        $conversationMessageStatusData = ConversationMessageStatusData::createFromArray([
            'user' => $user,
            'conversation' => $conversation,
            'type' => $this->entityManagerService->getReference(
                ConversationMessageStatusType::class,
                ConversationMessageStatusTypeConstant::UNREAD
            )
        ]);

        $conversationStatus = $this->conversationMessageStatusRepository
            ->oneByConversationUserType($conversationMessageStatusData)
            ->getQuery()
            ->getResult();

        return empty($conversationStatus);
    }

    public function changeConversationStatus(
        Conversation $conversation,
        User $user,
        int $status
    ): void {
        /** @var ConversationMessageStatus|null $conversationStatus */
        $conversationStatus = $this->conversationMessageStatusRepository
            ->oneByConversationUser($conversation, $user)
            ->getQuery()
            ->getOneOrNullResult();

        if ($conversationStatus === null) {
            return;
        }

        $conversationStatus->setType($this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            $status
        ));

        $this->entityManagerService->persistAndFlush($conversationStatus);
    }

    public function isConversationMessageRead(
        ConversationMessage $conversationMessage,
        User $user,
        bool $switchToRead = false
    ): bool {
        /** @var ConversationMessageStatus|null $conversationMessageStatus */
        $conversationMessageStatus = $this->conversationMessageStatusRepository
            ->oneByMessageUser($conversationMessage, $user)
            ->getQuery()
            ->getOneOrNullResult();

        if ($conversationMessageStatus !== null) {
            if ($conversationMessageStatus->getType()->getId() === ConversationMessageStatusTypeConstant::READ) {
                return true;
            }

            if ($switchToRead === true) {
                $conversationMessageStatus->setType($this->entityManagerService->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::READ
                ));

                $this->entityManagerService->persistAndFlush($conversationMessageStatus);
            }

            return false;
        }

        return true;
    }

    public function changeConversationMessageStatus(
        ConversationMessage $conversationMessage,
        User $user,
        int $status
    ): void {
        /** @var ConversationMessageStatus|null $conversationMessageStatus */
        $conversationMessageStatus = $this->conversationMessageStatusRepository
            ->oneByMessageUser($conversationMessage, $user)
            ->getQuery()
            ->getOneOrNullResult();

        if ($conversationMessageStatus === null) {
            return;
        }

        $conversationMessageStatus->setType($this->entityManagerService->getReference(ConversationMessageStatusType::class, $status));
        $this->entityManagerService->persistAndFlush($conversationMessageStatus);
    }

    public function setReadConversationMessage(
        ConversationMessage $conversationMessage,
        User $user
    ): void {
        $this->changeConversationMessageStatus($conversationMessage, $user, ConversationMessageStatusTypeConstant::READ);
    }

    public function getConversationMessageStatus(
        ConversationMessage $conversationMessage,
        User $user
    ): ?ConversationMessageStatus {
        return $this->conversationMessageStatusRepository
            ->oneByMessageUser($conversationMessage, $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
