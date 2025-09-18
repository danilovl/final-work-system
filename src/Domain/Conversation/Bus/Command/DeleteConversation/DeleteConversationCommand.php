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

namespace App\Domain\Conversation\Bus\Command\DeleteConversation;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Conversation\Entity\Conversation;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class DeleteConversationCommand implements CommandInterface
{
    private function __construct(public Conversation $conversation) {}

    public static function create(Conversation $conversation): self
    {
        return new self($conversation);
    }
}
