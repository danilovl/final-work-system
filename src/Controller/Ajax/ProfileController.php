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

namespace App\Controller\Ajax;

use App\Helper\FileHelper;
use App\Constant\{
    MediaTypeConstant,
    AjaxJsonTypeConstant,
    MediaMimeTypeTypeConstant
};
use App\Entity\Media;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class ProfileController extends BaseController
{
    public function createImageWebCamera(Request $request): JsonResponse
    {
        $imageData = json_decode($request->getContent(), true)['imageData'];

        $user = $this->getUser();
        $profileImage = $user->getProfileImage();
        $resizeImage = $this->get('app.resize_image');

        $media = $profileImage ?? new Media;
        if ($profileImage === null) {
            $mediaType = $this->get('app.facade.media_type')->find(MediaTypeConstant::USER_PROFILE_IMAGE);

            $media = new Media;
            $media->setType($mediaType);
            $media->setOwner($user);
        }

        $maxImageWidth = $this->get('danilovl.parameter')
            ->get('constraints.profile.image.maxWidth');

        $imageData = $resizeImage->resizeBase64Image($imageData, $maxImageWidth, true);
        $pngExtension = MediaMimeTypeTypeConstant::PNG['extension'];

        $tmpFilePath = FileHelper::createTmpFile(
            $pngExtension,
            base64_decode($imageData)
        );

        $uploadMedia = new UploadedFile(
            $tmpFilePath,
            sprintf('%s-%s.%s', 'web-camera-profile-image', uniqid(), $pngExtension),
            MediaMimeTypeTypeConstant::PNG['mimeType'],
            null,
            true
        );

        $media->setUploadMedia($uploadMedia);
        $this->persistAndFlush($media);

        if ($profileImage === null) {
            $user->setProfileImage($media);
            $this->flushEntity($user);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
    }
}
