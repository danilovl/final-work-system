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

namespace App\Domain\MediaCategory;

use App\Application\Traits\Model\SimpleInformationTrait;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\User\Entity\User;

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
