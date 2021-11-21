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

namespace App\Model\WorkCategory\Form\DataGrid;

use App\Model\WorkCategory\Repository\WorkCategoryRepository;
use Doctrine\ORM\QueryBuilder;
use App\Model\User\Entity\User;

class WorkCategoryDataGrid
{
    public function __construct(private WorkCategoryRepository $workCategoryRepository)
    {
    }

    public function queryBuilderWorkCategoriesByOwner(User $user): QueryBuilder
    {
        return $this->workCategoryRepository->allByOwner($user);
    }
}
