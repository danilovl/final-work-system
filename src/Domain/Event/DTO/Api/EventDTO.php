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

namespace App\Domain\Event\DTO\Api;

use App\Domain\EventType\DTO\Api\EventTypeDTO;
use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\EventAddress\DTO\Api\EventAddressDTO;
use Symfony\Component\Serializer\Attribute\Groups;

readonly class EventDTO
{
    public function __construct(
        #[Groups(['event:read'])]
        public int $id,
        #[Groups(['event:read'])]
        public ?string $name,
        #[Groups(['event:read'])]
        public string $start,
        #[Groups(['event:read'])]
        public string $end,
        #[Groups(['event-type:read'])]
        public EventTypeDTO $type,
        #[Groups(['event:owner:read'])]
        public UserDTO $owner,
        #[Groups(['event:event-address:read'])]
        public ?EventAddressDTO $address = null
    ) {}
}
