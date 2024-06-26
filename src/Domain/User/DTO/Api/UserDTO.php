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

namespace App\Domain\User\DTO\Api;

use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['user:read'])]
readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $username,
        public string $firstname,
        public string $lastname,
        public string $email,
        public ?string $degreeBefore,
        public ?string $degreeAfter
    ) {}
}
