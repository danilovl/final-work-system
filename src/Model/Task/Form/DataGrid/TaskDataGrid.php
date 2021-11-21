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

namespace App\Model\Task\Form\DataGrid;

use App\DataTransferObject\Repository\WorkData;
use App\Model\User\Entity\User;
use App\Model\Work\Facade\WorkFacade;
use Doctrine\ORM\QueryBuilder;

class TaskDataGrid
{
    public function __construct(private WorkFacade $workFacade)
    {
    }

    public function queryBuilderWorksBySupervisor(
        User $user,
        array $workStatus = null
    ): QueryBuilder {
        $workData = WorkData::createFromArray([
            'supervisor' => $user,
            'workStatus' => $workStatus
        ]);

        return $this->workFacade->getQueryBuilderWorksBySupervisor($workData);
    }
}
