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

namespace App\Domain\ConversationMessageStatus\DTO\Api;

use App\Domain\ConversationMessageStatusType\DTO\Api\ConversationMessageStatusTypeDTO;
use App\Domain\User\DTO\Api\UserDTO;

readonly class ConversationMessageStatusDTO
{
    public function __construct(
        public int $id,
        public ConversationMessageStatusTypeDTO $type,
        public UserDTO $user
    ) {}
}
