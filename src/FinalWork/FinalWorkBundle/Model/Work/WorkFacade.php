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

namespace FinalWork\FinalWorkBundle\Model\Work;

use Doctrine\ORM\EntityManager;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    WorkStatus
};
use FinalWork\FinalWorkBundle\Entity\Repository\WorkRepository;
use FinalWork\SonataUserBundle\Entity\User;

class WorkFacade
{
    /**
     * @var WorkRepository
     */
    private $workRepository;

    /**
     * WorkFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->workRepository = $entityManager->getRepository(Work::class);
    }

    /**
     * @param int $id
     * @return Work|null
     */
    public function find(int $id): ?Work
    {
        return $this->workRepository
            ->find($id);
    }

    /**
     * @param int|null $limit
     * @return array
     */
    public function findAll(?int $limit = null): array
    {
        return $this->workRepository
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
    public function getWorksByUserStatus(
        User $user,
        User $supervisor,
        string $type,
        $workStatus = null
    ): array {
        return $this->workRepository
            ->findAllByUserStatus($user, $supervisor, $type, $workStatus)
            ->getQuery()
            ->getResult();
    }
}
