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

namespace App\Domain\Task\DTO\Api;

use App\Domain\Work\DTO\Api\WorkDTO;
use DateTime;
use Symfony\Component\Serializer\Attribute\Groups;

readonly class TaskDTO
{
    public function __construct(
        #[Groups(['task:read'])]
        public int $id,
        #[Groups(['task:read'])]
        public bool $active,
        #[Groups(['task:read'])]
        public string $name,
        #[Groups(['task:read'])]
        public ?string $description,
        #[Groups(['task:read'])]
        public bool $complete,
        #[Groups(['task:read'])]
        public bool $notifyComplete,
        #[Groups(['task:read'])]
        public ?DateTime $deadline,
        #[Groups(['work:read'])]
        public ?WorkDTO $work
    ) {}
}
