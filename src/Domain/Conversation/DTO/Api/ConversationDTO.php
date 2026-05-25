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

namespace App\Domain\Conversation\DTO\Api;

use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\Work\DTO\Api\WorkDTO;
use App\Domain\ConversationMessage\DTO\Api\ConversationMessageDTO;
use Symfony\Component\Serializer\Attribute\Groups;

readonly class ConversationDTO
{
    public function __construct(
        #[Groups(['conversation:read'])]
        public int $id,
        #[Groups(['conversation:read'])]
        public ?string $name,
        #[Groups(['conversation:read'])]
        public bool $isRead,
        #[Groups(['conversation:read'])]
        public ?UserDTO $recipient,
        #[Groups(['work:read'])]
        public ?WorkDTO $work,
        #[Groups(['conversation:read'])]
        public ?ConversationMessageDTO $lastMessage
    ) {}
}
