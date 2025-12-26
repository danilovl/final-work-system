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

namespace App\Domain\EventAddress\DTO\Api;

use App\Domain\User\DTO\Api\UserDTO;
use Symfony\Component\Serializer\Attribute\Groups;

readonly class EventAddressDTO
{
    public function __construct(
        #[Groups(['event-address:read'])]
        public int $id,
        #[Groups(['event-address:read'])]
        public string $name,
        #[Groups(['event-address:read'])]
        public ?string $description,
        #[Groups(['event-address:read'])]
        public ?string $street,
        #[Groups(['event-address:read'])]
        public bool $skype,
        #[Groups(['event-address:read'])]
        public ?float $latitude,
        #[Groups(['event-address:read'])]
        public ?float $longitude,
        #[Groups(['event-address:owner:read'])]
        public UserDTO $owner,
    ) {}
}