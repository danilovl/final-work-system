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

namespace App\Domain\ConversationType\Bus\Query\ConversationTypeList;

use App\Domain\ConversationType\Entity\ConversationType;

readonly class GetConversationTypeListQueryResult
{
    /**
     * @param ConversationType[] $conversationTypes
     */
    public function __construct(
        public array $conversationTypes
    ) {}
}
