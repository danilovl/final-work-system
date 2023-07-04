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

namespace App\Domain\Conversation\Service;

use App\Application\Constant\ConversationMessageStatusTypeConstant;
use App\Application\Service\EntityManagerService;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatus\DataTransferObject\ConversationMessageStatusRepositoryData;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationMessageStatus\Repository\ConversationMessageStatusRepository;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;

readonly class ConversationStatusService
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private ConversationMessageStatusRepository $conversationMessageStatusRepository
    ) {}

    public function isConversationRead(
        Conversation $conversation,
        User $user
    ): bool {
        /** @var ConversationMessageStatusType $type */
        $type = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            ConversationMessageStatusTypeConstant::UNREAD->value
        );

        $conversationMessageStatusData = ConversationMessageStatusRepositoryData::createFromArray([
            'user' => $user,
            'conversation' => $conversation,
            'type' => $type
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

        /** @var ConversationMessageStatusType $conversationMessageStatusType */
        $conversationMessageStatusType = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            $status
        );

        $conversationStatus->setType($conversationMessageStatusType);

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
            if ($conversationMessageStatus->getType()->getId() === ConversationMessageStatusTypeConstant::READ->value) {
                return true;
            }

            if ($switchToRead === true) {
                /** @var ConversationMessageStatusType $conversationMessageStatusType */
                $conversationMessageStatusType = $this->entityManagerService->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::READ->value
                );

                $conversationMessageStatus->setType($conversationMessageStatusType);

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

        /** @var ConversationMessageStatusType $conversationMessageStatusType */
        $conversationMessageStatusType = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            $status
        );

        $conversationMessageStatus->setType($conversationMessageStatusType);
        $this->entityManagerService->persistAndFlush($conversationMessageStatus);
    }

    public function setReadConversationMessage(
        ConversationMessage $conversationMessage,
        User $user
    ): void {
        $this->changeConversationMessageStatus($conversationMessage, $user, ConversationMessageStatusTypeConstant::READ->value);
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
