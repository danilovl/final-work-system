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

namespace App\Domain\Media\Model;

use App\Domain\Media\Entity\Media;
use App\Domain\MediaMimeType\Entity\MediaMimeType;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaModel
{
    public string $name;

    public ?string $description = null;

    public User $owner;

    public MediaType $type;

    public MediaMimeType $mimeType;

    public Collection $categories;

    public ?Work $work = null;

    public string $mediaName = '';

    public ?string $originalMediaName = null;

    public ?string $originalExtension = null;

    public ?int $mediaSize = null;

    public bool $active = false;

    public ?UploadedFile $uploadMedia = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection;
        $this->mimeType = new MediaMimeType;
    }

    public static function fromMedia(Media $media): self
    {
        $model = new self;
        $model->name = $media->getName();
        $model->description = $media->getDescription();
        $model->owner = $media->getOwner();
        $model->type = $media->getType();
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
