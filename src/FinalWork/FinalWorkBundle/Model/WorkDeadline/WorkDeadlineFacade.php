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

namespace FinalWork\FinalWorkBundle\Model\WorkDeadline;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FinalWork\FinalWorkBundle\Entity\Work;
use FinalWork\SonataUserBundle\Entity\User;

class WorkDeadlineFacade
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * WorkDeadlineFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param User $user
     * @param int $limit
     * @return ArrayCollection
     */
    public function getWorkDeadlinesBySupervisor(User $user, int $limit = null): ArrayCollection
    {
        $workDeadLinesQuery = $this->em
            ->getRepository(Work::class)
            ->getWorkDeadlineBySupervisor($user)
            ->getQuery();

        if ($limit !== null) {
            $workDeadLinesQuery->setMaxResults($limit);
        }

        $workDeadLinesArrayResult = $workDeadLinesQuery->getArrayResult();

        $workDeadLines = new ArrayCollection;
        foreach ($workDeadLinesArrayResult as $workDeadLine) {
            $workDeadLines->add($workDeadLine['deadline']);
        }

        return $workDeadLines;
    }

    /**
     * @param User $user
     * @param int $limit
     * @return ArrayCollection
     */
    public function getWorkProgramDeadlinesBySupervisor(User $user, int $limit = null): ArrayCollection
    {
        $workProgramDeadLinesQuery = $this->em
            ->getRepository(Work::class)
            ->getWorkProgramDeadlineBySupervisor($user)
            ->getQuery();

        if ($limit !== null) {
            $workProgramDeadLinesQuery->setMaxResults($limit);
        }

        $workProgramDeadLines = new ArrayCollection;

        $workProgramDeadLinesArrayResult = $workProgramDeadLinesQuery->getArrayResult();
        foreach ($workProgramDeadLinesArrayResult as $workProgramDeadLine) {
            $workProgramDeadLines->add($workProgramDeadLine['deadlineProgram']);
        }

        return $workProgramDeadLines;
    }
}
