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

namespace App\Domain\ConversationParticipant\DTO\Api;

use App\Domain\User\DTO\Api\UserDTO;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['participant:read'])]
readonly class ParticipantDTO
{
    public function __construct(
        #[Groups(['participant:read'])]
        public int $id,
        #[Groups(['participant:read'])]
        public UserDTO $user
    ) {}
}