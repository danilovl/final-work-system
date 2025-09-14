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

namespace App\Domain\Conversation\Bus\Command\CreateConversation;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\ConversationMessage\Model\ConversationComposeMessageModel;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateConversationCommand implements CommandInterface
{
    private function __construct(
        public User $user,
        public ConversationComposeMessageModel $conversationComposeMessageModel
    ) {}

    public static function create(
        User $user,
        ConversationComposeMessageModel $conversationComposeMessageModel
    ): self {
        return new self($user, $conversationComposeMessageModel);
    }
}
