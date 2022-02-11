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

namespace App\Domain\WorkCategory;

use App\Domain\User\Entity\User;
use App\Domain\WorkCategory\Entity\WorkCategory;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use App\Domain\Traits\SimpleInformationTrait;

class WorkCategoryModel
{
    use SimpleInformationTrait;

    public ?string $sorting = null;
    public ?User $owner = null;
    public ?Collection $works = null;

    public function __construct()
    {
        $this->works = new ArrayCollection;
    }

    public static function fromMedia(WorkCategory $workCategory): self
    {
        $model = new self;
        $model->name = $workCategory->getName();
        $model->description = $workCategory->getDescription();
        $model->sorting = $workCategory->getSorting();
        $model->owner = $workCategory->getOwner();
        $model->works = $workCategory->getWorks();

        return $model;
    }
}
