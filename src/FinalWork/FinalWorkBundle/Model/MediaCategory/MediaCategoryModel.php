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

namespace FinalWork\FinalWorkBundle\Model\MediaCategory;

use FinalWork\FinalWorkBundle\Model\Traits\SimpleInformationTrait;
use FinalWork\FinalWorkBundle\Entity\MediaCategory;
use Symfony\Component\Validator\Constraints as Assert;

class MediaCategoryModel
{
    use SimpleInformationTrait;

    /**
     * @Assert\NotBlank()
     */
    public $owner;

    /**
     * @param MediaCategory $mediaCategory
     * @return MediaCategoryModel
     */
    public static function fromMediaCategory(MediaCategory $mediaCategory): self
    {
        $model = new self();
        $model->name = $mediaCategory->getName();
        $model->description = $mediaCategory->getDescription();
        $model->owner = $mediaCategory->getOwner();

        return $model;
    }
}
