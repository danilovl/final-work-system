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

namespace App\Domain\ConversationMessage\Bus\Query\MessagesByConversation;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\Conversation\Entity\Conversation;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetMessagesByConversationQuery implements QueryInterface
{
    private function __construct(
        public Conversation $conversation,
        public int $limit
    ) {}

    public static function create(
        Conversation $conversation,
        int $limit
    ): self {
        return new self($conversation, $limit);
    }
}
