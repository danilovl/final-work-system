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

use App\Application\DTO\Api\Output\BaseListOutput;
use App\Domain\User\DTO\Api\UserDTO;

readonly class UserSearchOutput extends BaseListOutput
{
    /**
     * @return UserDTO[]
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
