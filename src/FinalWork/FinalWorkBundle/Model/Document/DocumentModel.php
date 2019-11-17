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

namespace FinalWork\FinalWorkBundle\Model\Document;

use FinalWork\FinalWorkBundle\Entity\Media;
use FinalWork\FinalWorkBundle\Model\Traits\{
    ActiveTrait,
    SimpleInformationTrait
};
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class DocumentModel
{
    use SimpleInformationTrait;
    use ActiveTrait;

    /**
     * @Assert\NotBlank()
     */
    public $categories;

    /**
     * @var UploadedFile|null
     * @Assert\File()
     */
    public $uploadMedia;

    /**
     * @param Media $media
     * @return DocumentModel
     */
    public static function fromMedia(Media $media): self
    {
        $model = new self();
        $model->name = $media->getName();
        $model->description = $media->getDescription();
        $model->categories = $media->getCategories();
        $model->uploadMedia = $media->getUploadMedia();

        return $model;
    }
}
