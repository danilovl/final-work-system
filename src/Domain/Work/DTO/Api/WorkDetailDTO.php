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

namespace App\Domain\Work\DTO\Api;

use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\WorkStatus\DTO\Api\WorkStatusDTO;
use App\Domain\WorkType\DTO\Api\WorkTypeDTO;

/**
 * Work in /api/key responses: keys of the former api_key_field "Work" output, dates as Y-m-d H:i:s.
 */
readonly class WorkDetailDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public WorkTypeDTO $type,
        public WorkStatusDTO $status,
        public string $deadline,
        public ?string $deadlineProgram,
        public UserDTO $author,
        public UserDTO $supervisor,
        public ?UserDTO $opponent,
        public ?UserDTO $consultant
    ) {}
}
