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

namespace App\Domain\ConversationMessage\DTO\Api;

use App\Domain\ConversationMessageStatus\DTO\Api\ConversationMessageStatusDTO;
use App\Domain\User\DTO\Api\UserDTO;

/**
 * Message in the work conversation tab (/api/key/conversations/works/{id}/messages).
 */
readonly class WorkConversationMessageDTO
{
    /**
     * @param ConversationMessageStatusDTO[] $statuses
     */
    public function __construct(
        public int $id,
        public bool $read,
        public string $content,
        public UserDTO $owner,
        public array $statuses,
        public string $createdAt
    ) {}
}
