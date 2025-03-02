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

namespace App\Domain\MediaCategory\Model;

use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\User\Entity\User;

class MediaCategoryModel
{
    public string $name;

    public ?string $description = null;

    public User $owner;

    public static function fromMediaCategory(MediaCategory $mediaCategory): self
    {
        $model = new self;
        $model->name = $mediaCategory->getName();
        $model->description = $mediaCategory->getDescription();
        $model->owner = $mediaCategory->getOwner();

        return $model;
    }
}
