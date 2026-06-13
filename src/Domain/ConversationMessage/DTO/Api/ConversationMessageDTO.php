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

use App\Domain\User\DTO\Api\UserDTO;
use Symfony\Component\Serializer\Attribute\Groups;

readonly class ConversationMessageDTO
{
    public function __construct(
        #[Groups(['conversation:read'])]
        public int $id,
        #[Groups(['conversation:read'])]
        public UserDTO $owner,
        #[Groups(['conversation:read'])]
        public string $createdAt
    ) {}
}
