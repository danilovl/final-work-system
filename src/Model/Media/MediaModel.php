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

namespace App\Model\Media;

use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use App\Entity\{
    Work,
    Media,
    MediaType,
    MediaMimeType
};
use App\Model\Traits\SimpleInformationTrait;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaModel
{
    use SimpleInformationTrait;

    public ?User $owner = null;
    public ?MediaType $type = null;
    public ?MediaMimeType $mimeType = null;
    public ?Collection $categories = null;
    public ?Work $work = null;
    public ?string $mediaName = null;
    public ?string $originalMediaName = null;
    public ?string $originalExtension = null;
    public ?int $mediaSize = null;
    public bool $active = false;
    public ?UploadedFile $uploadMedia = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection;
    }

    public static function fromMedia(Media $media): self
    {
        $model = new self;
        $model->name = $media->getName();
        $model->description = $media->getDescription();
        $model->owner = $media->getOwner();
        $model->mimeType = $media->getMimeType();
        $model->categories = $media->getCategories();
        $model->work = $media->getWork();
        $model->mediaName = $media->getMediaName();
        $model->originalMediaName = $media->getOriginalMediaName();
        $model->originalExtension = $media->getOriginalExtension();
        $model->mediaSize = $media->getMediaSize();
        $model->active = $media->isActive();

        return $model;
    }
}
