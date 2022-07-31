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

namespace App\Domain\WorkCategory\Factory;

use App\Application\Factory\Model\BaseModelFactory;
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\WorkCategoryModel;

class WorkCategoryFactory extends BaseModelFactory
{
    public function flushFromModel(
        WorkCategoryModel $workCategoryModel,
        WorkCategory $workCategory = null
    ): WorkCategory {
        $workCategory = $workCategory ?? new WorkCategory;
        $workCategory = $this->fromModel($workCategory, $workCategoryModel);

        $this->entityManagerService->persistAndFlush($workCategory);

        return $workCategory;
    }

    public function fromModel(
        WorkCategory $workCategory,
        WorkCategoryModel $workCategoryModel
    ): WorkCategory {
        $workCategory->setName($workCategoryModel->name);
        $workCategory->setDescription($workCategoryModel->description);
        $workCategory->setSorting($workCategoryModel->sorting);
        $workCategory->setWorks($workCategoryModel->works);
        $workCategory->setOwner($workCategoryModel->owner);

        return $workCategory;
    }
}
