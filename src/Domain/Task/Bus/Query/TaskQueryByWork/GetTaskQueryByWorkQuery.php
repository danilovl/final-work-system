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

namespace App\Domain\Task\Bus\Query\TaskQueryByWork;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\Work\Entity\Work;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetTaskQueryByWorkQuery implements QueryInterface
{
    private function __construct(
        public Work $work,
        public bool $active
    ) {}

    public static function create(
        Work $work,
        bool $active
    ): self {
        return new self($work, $active);
    }
}
