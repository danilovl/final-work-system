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

namespace App\Service;

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
    private ConversationMessageStatusRepository $conversationMessageStatusRepository;

    public function __construct(private EntityManagerService $em)
    {
        $this->conversationMessageStatusRepository = $this->em->getRepository(ConversationMessageStatus::class);
    }

    public function isConversationRead(
        Conversation $conversation,
        User $user
    ): bool {
        $conversationStatus = $this->conversationMessageStatusRepository
            ->oneByConversationUserType(
                $conversation,
                $user,
                $this->em->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::UNREAD
                )
            )
            ->getQuery()
            ->getResult();

        return empty($conversationStatus);
    }

    public function changeConversationStatus(
        Conversation $conversation,
        User $user,
        $status
    ): void {
        /** @var ConversationMessageStatus|null $conversationStatus */
        $conversationStatus = $this->conversationMessageStatusRepository
            ->oneByConversationUser($conversation, $user)
            ->getQuery()
            ->getOneOrNullResult();

        if ($conversationStatus === null) {
            return;
        }

        $conversationStatus->setType($this->em->getReference(ConversationMessageStatusType::class, $status));
        $this->em->persistAndFlush($conversationStatus);
    }

    public function isConversationMessageRead(
        ConversationMessage $conversationMessage,
        User $user,
        $switchToRead = false
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
                $conversationMessageStatus->setType($this->em->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::READ
                ));

                $this->em->persistAndFlush($conversationMessageStatus);
            }

            return false;
        }

        return true;
    }

    public function changeConversationMessageStatus(
        ConversationMessage $conversationMessage,
        User $user,
        $status
    ): void {
        /** @var ConversationMessageStatus|null $conversationMessageStatus */
        $conversationMessageStatus = $this->conversationMessageStatusRepository
            ->oneByMessageUser($conversationMessage, $user)
            ->getQuery()
            ->getOneOrNullResult();

        if ($conversationMessageStatus === null) {
            return;
        }

        $conversationMessageStatus->setType($this->em->getReference(ConversationMessageStatusType::class, $status));
        $this->em->persistAndFlush($conversationMessageStatus);
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
