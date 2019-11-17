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

namespace FinalWork\FinalWorkBundle\DataGrid;

use Doctrine\ORM\{
    QueryBuilder,
    EntityManager
};
use FinalWork\FinalWorkBundle\Entity\Repository\WorkStatusRepository;
use FinalWork\FinalWorkBundle\Entity\WorkStatus;

class WorkStatusDataGrid
{
    /**
     * @var WorkStatusRepository
     */
    private $workStatusRepository;

    /**
     * WorkStatusDataGrid constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->workStatusRepository = $entityManager->getRepository(WorkStatus::class);
    }

    /**
     * @return QueryBuilder
     */
    public function queryBuilder(): QueryBuilder
    {
        return $this->workStatusRepository
            ->createQueryBuilder('status')
            ->orderBy('status.name', 'ASC');
    }
}
