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

namespace App\Domain\User\DTO\Api\Output;

use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\Work\DTO\Api\WorkDTO;

readonly class UserListItemDTO
{
    /**
     * @param UserDTO $user
     * @param WorkDTO[] $works
     */
    public function __construct(
        public UserDTO $user,
        public array $works
    ) {}
}
