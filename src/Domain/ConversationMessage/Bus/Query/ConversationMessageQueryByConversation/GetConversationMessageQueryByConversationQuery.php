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

namespace App\Domain\ConversationMessage\Bus\Query\ConversationMessageQueryByConversation;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use App\Domain\Conversation\Entity\Conversation;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetConversationMessageQueryByConversationQuery implements QueryInterface
{
    private function __construct(
        public Conversation $conversation
    ) {}

    public static function create(Conversation $conversation): self
    {
        return new self($conversation);
    }
}
