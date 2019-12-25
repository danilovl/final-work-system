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

namespace FinalWork\FinalWorkBundle\Model\Media;

use Doctrine\Common\Collections\ArrayCollection;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Media,
    MediaType,
    MediaMimeType
};
use FinalWork\FinalWorkBundle\Model\Traits\SimpleInformationTrait;
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaModel
{
    use SimpleInformationTrait;

    /**
     * @var User|null
     */
    public $owner;

    /**
     * @var MediaType|null
     */
    public $type;

    /**
     * @var MediaMimeType|null
     */
    public $mimeType;

    /**
     * @var ArrayCollection
     */
    public $categories;

    /**
     * @var Work|null
     */
    public $work;

    /**
     * @var string|null
     */
    public $mediaName;

    /**
     * @var string|null
     */
    public $originalMediaName;

    /**
     * @var string|null
     */
    public $originalExtension;

    /**
     * @var int|null
     */
    public $mediaSize;

    /**
     * @var UploadedFile|null
     */
    public $uploadMedia;

    /**
     * MediaModel constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection;
    }

    /**
     * @param Media $media
     * @return MediaModel
     */
    public static function fromMedia(Media $media): self
    {
        $model = new self();
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
        $model->uploadMedia = $media->getUploadMedia();

        return $model;
    }
}
