<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Controller;

use App\Constant\{
    MediaTypeConstant,
    DateFormatConstant
};
use App\Exception\RuntimeException;
use App\Helper\{
    MediaHelper,
    FunctionHelper
};
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Entity\{
    Work,
    Media
};
use Symfony\Component\Filesystem\Filesystem;
use App\Interfaces\MediaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaBaseController extends BaseController implements MediaInterface
{
    public function createMedia(
        Media $media,
        int $mediaType,
        ?Work $work = null
    ): void {
        $uploadMedia = $media->getUploadMedia();
        $originalMediaName = $uploadMedia->getClientOriginalName();
        $originalMediaExtension = $uploadMedia->getClientOriginalExtension();
        $mimeType = $uploadMedia->getMimeType();
        $mediaSize = $uploadMedia->getSize();

        $mediaMimeType = $this->get('app.facade.media.mime_type')
            ->getMimeTypeByName($mimeType);
        if ($mediaMimeType === null) {
            throw new RuntimeException("MediaMimeType doesn't exist");
        }

        $mediaType = $this->get('app.facade.media_type')
            ->find($mediaType);
        if ($mediaType === null) {
            throw new RuntimeException("MediaType doesn't exist");
        }

        $mediaName = MediaHelper::generateMediaNameByType($mediaMimeType);
        $media->setMediaName($mediaName);
        $media->setMimeType($mediaMimeType);
        $media->setType($mediaType);
        $media->setOwner($this->getUser());

        if ($work !== null) {
            $media->setWork($work);
        }

        $media->setOriginalMediaName($originalMediaName);
        $media->setOriginalExtension($originalMediaExtension);
        $media->setMediaSize($mediaSize);

        $this->createEntity($media);

        $uploadMedia->move(
            $media->getUploadDir(),
            $mediaName
        );
    }

    public function editMedia(
        Media $media,
        ?Work $work = null
    ): void {
        $fs = new Filesystem;

        $uploadMedia = $media->getUploadMedia();
        if ($uploadMedia) {
            $originalMediaName = $uploadMedia->getClientOriginalName();
            $originalMediaExtension = $uploadMedia->getClientOriginalExtension();
            $mimeType = $uploadMedia->getMimeType();
            $mediaSize = $uploadMedia->getSize();

            $mediaMimeType = $this->get('app.facade.media.mime_type')
                ->getMimeTypeByName($mimeType);
            if ($mediaMimeType === null) {
                throw new RuntimeException("MediaMimeType doesn't exist");
            }

            $olMedia = $this->getParameter('upload_directory') . DIRECTORY_SEPARATOR . $media->getMediaNameFolderType();
            if (!$fs->exists($olMedia)) {
                throw new RuntimeException("Media doesn't exist");
            }

            $fs->remove($olMedia);

            $mediaName = MediaHelper::generateMediaNameByType($mediaMimeType);
            $media->setMediaName($mediaName);
            $media->setMimeType($mediaMimeType);
            $media->setOwner($this->getUser());

            if ($work !== null) {
                $media->setWork($work);
            }

            $media->setOriginalMediaName($originalMediaName);
            $media->setOriginalExtension($originalMediaExtension);
            $media->setMediaSize($mediaSize);

            $uploadMedia->move(
                $media->getUploadDir(),
                $mediaName
            );
        }

        $this->flushEntity();
    }

    public function downloadMedia(Media $media): BinaryFileResponse
    {
        $filePath = $media->getWebPath();
        $isExistFile = (new Filesystem)->exists($filePath);

        if (!$isExistFile) {
            throw new NotFoundHttpException("File doesn't exist");
        }

        $name = FunctionHelper::sanitizeFileName($media->getName());
        $date = $media->getCreatedAt()->format(DateFormatConstant::DATE);
        $extension = $media->getMimeType()->getExtension();

        $fileName = sprintf('%s.%s', $name, $extension);
        if ($media->getType()->getId() === MediaTypeConstant::WORK_VERSION) {
            $type = $media->getWork() ? $media->getWork()->getType()->getShortcut() : '';
            $fileName = sprintf('%s_%s_%s.%s', $date, $type, $name, $extension);
        }

        $response = $this->file($filePath, $fileName);
        $response->headers->set('Content-Type', $media->getMimeType());

        return $response->send();
    }

    public function deleteMedia(Media $media): void
    {
        (new Filesystem)->remove($media->getWebPath());
        $this->removeEntity($media);
    }
}
