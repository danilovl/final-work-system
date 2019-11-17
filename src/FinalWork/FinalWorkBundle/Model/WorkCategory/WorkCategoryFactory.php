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

namespace FinalWork\FinalWorkBundle\Model\WorkCategory;

use Exception;
use FinalWork\FinalWorkBundle\Entity\WorkCategory;
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};

class WorkCategoryFactory extends BaseModelFactory
{
    /**
     * @param WorkCategoryModel $workCategoryModel
     * @param WorkCategory|null $workCategory
     * @return WorkCategory
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function flushFromModel(
        WorkCategoryModel $workCategoryModel,
        WorkCategory $workCategory = null
    ): WorkCategory {
        $workCategory = $workCategory ?? new WorkCategory;
        $workCategory = $this->fromModel($workCategory, $workCategoryModel);

        $this->em->persist($workCategory);
        $this->em->flush();

        return $workCategory;
    }

    /**
     * @param WorkCategory $workCategory
     * @param WorkCategoryModel $workCategoryModel
     * @return WorkCategory
     * @throws Exception
     */
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
