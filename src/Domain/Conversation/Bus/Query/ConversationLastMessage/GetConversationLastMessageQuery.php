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

namespace App\Domain\Conversation\Bus\Query\ConversationLastMessage;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\Conversation\Entity\Conversation;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetConversationLastMessageQuery implements QueryInterface
{
    private function __construct(
        public Conversation $conversation,
        public ?string $search
    ) {}

    public static function create(
        Conversation $conversation,
        ?string $search
    ): self {
        return new self($conversation, $search);
    }
}
