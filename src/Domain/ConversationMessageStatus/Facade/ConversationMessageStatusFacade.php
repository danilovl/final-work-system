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

namespace App\Domain\ConversationMessageStatus\Facade;

use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationMessageStatus\Repository\ConversationMessageStatusRepository;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;

readonly class ConversationMessageStatusFacade
{
    public function __construct(private ConversationMessageStatusRepository $conversationMessageStatusRepository) {}

    public function updateAllToStatus(
        User $user,
        ConversationMessageStatusType $conversationMessageStatusType
    ): void {
        $this->conversationMessageStatusRepository->updateAllToStatus($user, $conversationMessageStatusType);
    }

    public function findByMessageUser(ConversationMessage $conversationMessage, User $user): ?ConversationMessageStatus
    {
        /** @var ConversationMessageStatus|null $result */
        $result = $this->conversationMessageStatusRepository
            ->oneByMessageUser($conversationMessage, $user)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return array<int|string>
     */
    public function listConversationIdsByUserType(User $user, ConversationMessageStatusType $type): array
    {
        /** @var array<int|string> $result */
        $result = $this->conversationMessageStatusRepository
            ->conversationIdsByUserType($user, $type)
            ->getQuery()
            ->getSingleColumnResult();

        return $result;
    }

    /**
     * @return array<array{messageId: int|string, typeId: int|string}>
     */
    public function listMessageTypesByConversationUser(int $conversationId, User $user): array
    {
        /** @var array<array{messageId: int|string, typeId: int|string}> $result */
        $result = $this->conversationMessageStatusRepository
            ->messageTypesByConversationUser($conversationId, $user)
            ->getQuery()
            ->getArrayResult();

        return $result;
    }
}
