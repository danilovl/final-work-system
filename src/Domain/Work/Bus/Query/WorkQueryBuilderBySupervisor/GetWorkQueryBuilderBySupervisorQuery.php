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

namespace App\Domain\Work\Bus\Query\WorkQueryBuilderBySupervisor;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\Work\DTO\Repository\WorkRepositoryDTO;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetWorkQueryBuilderBySupervisorQuery implements QueryInterface
{
    private function __construct(
        public WorkRepositoryDTO $workRepositoryDTO
    ) {}

    public static function create(WorkRepositoryDTO $workRepositoryDTO): self
    {
        return new self($workRepositoryDTO);
    }
}
