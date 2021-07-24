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

namespace App\Form\DataGrid;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use App\Repository\WorkStatusRepository;

class WorkStatusDataGrid
{
    public function __construct(private WorkStatusRepository $workStatusRepository)
    {
    }

    public function queryBuilder(): QueryBuilder
    {
        return $this->workStatusRepository
            ->createQueryBuilder('status')
            ->orderBy('status.name', Criteria::ASC);
    }
}
