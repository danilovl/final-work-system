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

namespace FinalWork\FinalWorkBundle\Model\WorkStatus;

use Doctrine\ORM\EntityManager;
use FinalWork\FinalWorkBundle\Entity\WorkStatus;
use FinalWork\FinalWorkBundle\Entity\Repository\WorkStatusRepository;
use FinalWork\SonataUserBundle\Entity\User;

class WorkStatusFacade
{
    /**
     * @var WorkStatusRepository
     */
    private $workStatusRepository;

    /**
     * WorkStatusFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->workStatusRepository = $entityManager->getRepository(WorkStatus::class);
    }

    /**
     * @param int $id
     * @return WorkStatus|null
     */
    public function find(int $id): ?WorkStatus
    {
        return $this->workStatusRepository
            ->find($id);
    }

    /**
     * @param int|null $limit
     * @return array
     */
    public function findAll(int $limit = null): array
    {
        return $this->workStatusRepository
            ->baseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param User $supervisor
     * @param string $type
     * @param WorkStatus|iterable|null $workStatus
     * @return array
     */
    public function getCountByUser(
        User $user,
        User $supervisor,
        string $type,
        $workStatus
    ): array {
        return $this->workStatusRepository
            ->getCountByUser($user, $supervisor, $type, $workStatus)
            ->getQuery()
            ->getResult();
    }
}
