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

namespace App\Domain\Work\Facade;

use App\Domain\Work\Entity\Work;
use App\Domain\Work\Repository\WorkRepository;
use App\Domain\User\Entity\User;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;

readonly class WorkDeadlineFacade
{
    public function __construct(private WorkRepository $workRepository) {}

    /**
     * @return Work[]
     */
    public function listAfterDeadline(int $offset, int $limit): array
    {
        /** @var Work[] $result */
        $result = $this->workRepository
            ->getWorksAfterDeadline()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return ArrayCollection<DateTimeImmutable>
     */
    public function listWorkDeadlinesBySupervisor(User $user, ?int $limit = null): ArrayCollection
    {
        $workDeadLinesQuery = $this->workRepository
            ->workDeadlineBySupervisor($user)
            ->getQuery();

        if ($limit !== null) {
            $workDeadLinesQuery->setMaxResults($limit);
        }

        /** @var array<array{deadline: string}> $workDeadLinesArrayResult */
        $workDeadLinesArrayResult = $workDeadLinesQuery->getArrayResult();

        $workDeadLines = new ArrayCollection;
        foreach ($workDeadLinesArrayResult as $workDeadLine) {
            $workDeadLines->add($workDeadLine['deadline']);
        }

        return $workDeadLines;
    }

    /**
     * @return ArrayCollection<DateTimeImmutable>
     */
    public function listWorkProgramDeadlinesBySupervisor(User $user, ?int $limit = null): ArrayCollection
    {
        $workProgramDeadLinesQuery = $this->workRepository
            ->workProgramDeadlineBySupervisor($user)
            ->getQuery();

        if ($limit !== null) {
            $workProgramDeadLinesQuery->setMaxResults($limit);
        }

        $workProgramDeadLines = new ArrayCollection;

        /** @var array<array{deadlineProgram: string}> $workProgramDeadLinesArrayResult */
        $workProgramDeadLinesArrayResult = $workProgramDeadLinesQuery->getArrayResult();
        foreach ($workProgramDeadLinesArrayResult as $workProgramDeadLine) {
            $workProgramDeadLines->add($workProgramDeadLine['deadlineProgram']);
        }

        return $workProgramDeadLines;
    }
}
