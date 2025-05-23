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

use App\Domain\ConversationMessage\Entity\ConversationMessage;

readonly class GetConversationLastMessageQueryResult
{
    /**
     * @param ConversationMessage[] $conversationMessages
     */
    public function __construct(public array $conversationMessages) {}
}
