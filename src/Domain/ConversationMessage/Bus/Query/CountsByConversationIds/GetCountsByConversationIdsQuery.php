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

namespace App\Domain\ConversationMessage\Bus\Query\CountsByConversationIds;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetCountsByConversationIdsQuery implements QueryInterface
{
    private function __construct(
        public array $conversationIds
    ) {}

    public static function create(array $conversationIds): self
    {
        return new self($conversationIds);
    }
}
