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

namespace App\Domain\Conversation\Bus\Command\CreateConversationMessage;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Model\ConversationMessageModel;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateConversationMessageCommand implements CommandInterface
{
    private function __construct(
        public Conversation $conversation,
        public ConversationMessageModel $conversationMessageModel,
        public User $user
    ) {}

    public static function create(
        Conversation $conversation,
        ConversationMessageModel $conversationMessageModel,
        User $user,
    ): self {
        return new self(
            conversation: $conversation,
            conversationMessageModel: $conversationMessageModel,
            user: $user
        );
    }
}
