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

namespace App\Domain\ConversationMessageStatus\Bus\Query\MessageTypesByConversationUser;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetMessageTypesByConversationUserQuery implements QueryInterface
{
    private function __construct(
        public int $conversationId,
        public User $user
    ) {}

    public static function create(
        int $conversationId,
        User $user
    ): self {
        return new self($conversationId, $user);
    }
}
