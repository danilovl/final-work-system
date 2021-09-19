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

namespace App\Service;

use App\Constant\DateFormatConstant;
use App\Constant\MediaTypeConstant;
use App\Entity\Media;
use App\Helper\FunctionHelper;
use Symfony\Component\HttpFoundation\{
    ResponseHeaderBag,
    BinaryFileResponse
};
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaService
{
    public function download(Media $media): BinaryFileResponse
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

    public function file(
        mixed $file,
        string $fileName = null,
        string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT
    ): BinaryFileResponse {
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition($disposition, null === $fileName ? $response->getFile()->getFilename() : $fileName);

        return $response;
    }
}
