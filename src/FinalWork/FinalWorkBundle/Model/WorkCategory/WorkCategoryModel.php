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

use Doctrine\Common\Collections\ArrayCollection;
use FinalWork\FinalWorkBundle\Entity\WorkCategory;
use FinalWork\FinalWorkBundle\Model\Traits\SimpleInformationTrait;
use Symfony\Component\Validator\Constraints as Assert;

class WorkCategoryModel
{
    use SimpleInformationTrait;

    /**
     * @var string|null
     */
    public $sorting;

    /**
     * @Assert\NotBlank()
     */
    public $owner;

    /**
     * @Assert\NotBlank()
     */
    public $works;

    /**
     * WorkCategoryModel constructor.
     */
    public function __construct()
    {
        $this->works = new ArrayCollection;
    }

    /**
     * @param WorkCategory $workCategory
     * @return WorkCategoryModel
     */
    public static function fromMedia(WorkCategory $workCategory): self
    {
        $model = new self();
        $model->name = $workCategory->getName();
        $model->description = $workCategory->getDescription();
        $model->sorting = $workCategory->getSorting();
        $model->owner = $workCategory->getOwner();
        $model->works = $workCategory->getWorks();

        return $model;
    }
}
