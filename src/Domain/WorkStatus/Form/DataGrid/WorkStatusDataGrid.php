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

namespace App\Domain\WorkStatus\Form\DataGrid;

use App\Domain\WorkStatus\Facade\WorkStatusFacade;
use Doctrine\ORM\QueryBuilder;

class WorkStatusDataGrid
{
    public function __construct(private readonly WorkStatusFacade $workStatusFacade) {}

    public function queryBuilder(): QueryBuilder
    {
        return $this->workStatusFacade->queryBuilderAllOrderedByName();
    }
}
