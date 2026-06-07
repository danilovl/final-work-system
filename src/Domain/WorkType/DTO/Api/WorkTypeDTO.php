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

namespace App\Domain\WorkType\DTO\Api;

use Symfony\Component\Serializer\Attribute\Groups;

readonly class WorkTypeDTO
{
    public function __construct(
        #[Groups(['work_type:read'])]
        public int $id,
        #[Groups(['work_type:read'])]
        public string $name,
        #[Groups(['work_type:read'])]
        public ?string $description,
        #[Groups(['work_type:read'])]
        public string $shortcut
    ) {}
}
