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

namespace App\Domain\Work\Bus\Query\WorkUsers;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\Work\Entity\Work;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetWorkUsersQuery implements QueryInterface
{
    private function __construct(
        public Work $work,
        public bool $author,
        public bool $supervisor,
        public bool $consultant,
        public bool $opponent
    ) {}

    public static function create(
        Work $work,
        bool $author,
        bool $supervisor,
        bool $consultant,
        bool $opponent
    ): self {
        return new self($work, $author, $supervisor, $consultant, $opponent);
    }
}
