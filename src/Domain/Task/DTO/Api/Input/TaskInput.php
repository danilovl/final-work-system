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

namespace App\Domain\Task\DTO\Api\Input;

use App\Domain\Work\Entity\Work;
use App\Domain\Work\Validator\Constraints\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

readonly class TaskInput
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,
        public ?string $description,
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $deadline,
        #[Assert\Positive]
        #[EntityExists(class: Work::class)]
        public int $workId,
        public bool $complete = false,
        public bool $active = true
    ) {}
}
