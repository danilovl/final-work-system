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

namespace App\Domain\ConversationMessage\Bus\Query\LastByConversationIds;

use App\Domain\ConversationMessage\Entity\ConversationMessage;

readonly class GetLastByConversationIdsQueryResult
{
    /**
     * @param array<int, ConversationMessage|null> $messages
     */
    public function __construct(
        public array $messages
    ) {}
}
