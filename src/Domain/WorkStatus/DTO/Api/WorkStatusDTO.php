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

namespace App\Domain\WorkStatus\DTO\Api;

use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['work_status:read'])]
readonly class WorkStatusDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $color
    ) {}
}
