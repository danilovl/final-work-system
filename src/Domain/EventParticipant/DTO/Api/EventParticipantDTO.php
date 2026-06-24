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

namespace App\Domain\EventParticipant\DTO\Api;

use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\Work\DTO\Api\WorkDTO;
use Symfony\Component\Serializer\Attribute\Groups;

readonly class EventParticipantDTO
{
    public function __construct(
        #[Groups(['event-participant:read'])]
        public ?int $id,
        #[Groups(['event-participant:read'])]
        public ?string $firstName,
        #[Groups(['event-participant:read'])]
        public ?string $secondName,
        #[Groups(['event-participant:read'])]
        public ?string $email,
        #[Groups(['user:read'])]
        public ?UserDTO $user,
        #[Groups(['work:read'])]
        public ?WorkDTO $work
    ) {}
}
