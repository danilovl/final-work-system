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

namespace App\Domain\Document;

use App\Application\Traits\Model\{
    SimpleInformationTrait};
use App\Application\Traits\Model\ActiveTrait;
use App\Domain\Media\Entity\Media;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentModel
{
    use SimpleInformationTrait;
    use ActiveTrait;

    public ?Collection $categories = null;
    public ?UploadedFile $uploadMedia = null;

    public static function fromMedia(Media $media): self
    {
        $model = new self;
        $model->name = $media->getName();
        $model->description = $media->getDescription();
        $model->categories = $media->getCategories();
        $model->uploadMedia = $media->getUploadMedia();

        return $model;
    }
}
