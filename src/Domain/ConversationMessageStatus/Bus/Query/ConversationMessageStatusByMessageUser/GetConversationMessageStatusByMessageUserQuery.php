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

namespace App\Domain\ConversationMessageStatus\Bus\Query\ConversationMessageStatusByMessageUser;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetConversationMessageStatusByMessageUserQuery implements QueryInterface
{
    private function __construct(
        public ConversationMessage $conversationMessage,
        public User $user
    ) {}

    public static function create(
        ConversationMessage $conversationMessage,
        User $user
    ): self {
        return new self($conversationMessage, $user);
    }
}
