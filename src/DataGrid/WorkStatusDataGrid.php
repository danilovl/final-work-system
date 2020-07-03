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

namespace App\DataGrid;

use Doctrine\ORM\{
    QueryBuilder,
    EntityManager
};
use App\Repository\WorkStatusRepository;
use App\Entity\WorkStatus;

class WorkStatusDataGrid
{
    private WorkStatusRepository $workStatusRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->workStatusRepository = $entityManager->getRepository(WorkStatus::class);
    }

    public function queryBuilder(): QueryBuilder
    {
        return $this->workStatusRepository
            ->createQueryBuilder('status')
            ->orderBy('status.name', 'ASC');
    }
}
