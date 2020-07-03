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

namespace App\Model\MediaCategory;

use App\Model\Traits\SimpleInformationTrait;
use App\Entity\MediaCategory;
use App\Entity\User;

class MediaCategoryModel
{
    use SimpleInformationTrait;

    public ?User $owner = null;

    public static function fromMediaCategory(MediaCategory $mediaCategory): self
    {
        $model = new self;
        $model->name = $mediaCategory->getName();
        $model->description = $mediaCategory->getDescription();
        $model->owner = $mediaCategory->getOwner();

        return $model;
    }
}
