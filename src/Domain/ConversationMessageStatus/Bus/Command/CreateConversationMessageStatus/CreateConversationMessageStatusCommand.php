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

namespace App\Domain\ConversationMessageStatus\Bus\Command\CreateConversationMessageStatus;

use App\Infrastructure\Interfaces\Bus\CommandInterface;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateConversationMessageStatusCommand implements CommandInterface
{
    /**
     * @param iterable<ConversationParticipant|User> $participants
     */
    private function __construct(
        public Conversation $conversation,
        public ConversationMessage $message,
        public User $user,
        public iterable $participants,
        public int $type
    ) {}

    /**
     * @param iterable<ConversationParticipant|User> $participants
     */
    public static function create(
        Conversation $conversation,
        ConversationMessage $message,
        User $user,
        iterable $participants,
        int $type
    ): self {
        return new self($conversation, $message, $user, $participants, $type);
    }
}
