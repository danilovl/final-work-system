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

namespace App\Domain\ConversationMessageStatus\DTO\Repository;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;

class ConversationMessageStatusRepositoryDTO
{
    public function __construct(
        public ?User $user = null,
        public ?Conversation $conversation = null,
        public ?ConversationMessageStatusType $type = null
    ) {}
}
