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

namespace App\Domain\Media\Service;

use App\Application\Constant\DateFormatConstant;
use App\Application\Helper\FunctionHelper;
use App\Domain\Media\Entity\Media;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\{
    BinaryFileResponse,
    ResponseHeaderBag
};
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
        $date = $media->getCreatedAt()->format(DateFormatConstant::DATE->value);
        $extension = $media->getMimeType()->getExtension();

        $fileName = sprintf('%s.%s', $name, $extension);
        if ($media->getType()->getId() === MediaTypeConstant::WORK_VERSION->value) {
            $type = $media->getWork() ? $media->getWork()->getType()->getShortcut() : '';
            $fileName = sprintf('%s_%s_%s.%s', $date, $type, $name, $extension);
        }

        $response = $this->file($filePath, $fileName);
        $response->headers->set('Content-Type', $media->getMimeType()->getName());

        return $response->send();
    }

    public function file(
        string $file,
        string $fileName = null,
        string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT
    ): BinaryFileResponse {
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition($disposition, null === $fileName ? $response->getFile()->getFilename() : $fileName);

        return $response;
    }
}
