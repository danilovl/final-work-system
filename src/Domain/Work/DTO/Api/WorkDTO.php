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
use Symfony\Component\Serializer\Attribute\Groups;

readonly class WorkDTO
{
    public function __construct(
        #[Groups(['work:read'])]
        public int $id,
        #[Groups(['work:read'])]
        public string $title,
        #[Groups(['work:read'])]
        public ?string $shortcut,
        #[Groups(['work:read'])]
        public string $deadline,
        #[Groups(['work:read'])]
        public ?string $deadlineProgram,
        #[Groups(['work_status:read'])]
        public WorkStatusDTO $status,
        #[Groups(['work_type:read'])]
        public WorkTypeDTO $type,
        #[Groups(['user:read:author'])]
        public ?UserDTO $author,
        #[Groups(['user:read:supervisor'])]
        public ?UserDTO $supervisor,
        #[Groups(['user:read:opponent'])]
        public ?UserDTO $opponent,
        #[Groups(['user:read:consultant'])]
        public ?UserDTO $consultant
    ) {}
}
