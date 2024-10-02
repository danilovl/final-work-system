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

namespace App\Domain\Task\DTO\Api\Output;

use App\Domain\Task\DTO\Api\TaskDTO;

readonly class TaskListOwnerOutput extends BaseListOutput
{
    /**
     * @return TaskDTO[]
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
