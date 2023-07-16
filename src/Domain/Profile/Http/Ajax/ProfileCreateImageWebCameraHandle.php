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

namespace App\Domain\Profile\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FileHelper;
use App\Application\Service\{
    RequestService,
    ResizeImageService,
    EntityManagerService
};
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Facade\MediaTypeFacade;
use App\Domain\MediaMimeType\Constant\MediaMimeTypeTypeConstant;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\User\Service\UserService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class ProfileCreateImageWebCameraHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private ResizeImageService $resizeImageService,
        private MediaTypeFacade $mediaTypeFacade,
        private ParameterServiceInterface $parameterService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $imageData = json_decode($request->getContent(), true)['imageData'];

        $user = $this->userService->getUser();
        $profileImage = $user->getProfileImage();

        $media = $profileImage ?? new Media;
        if ($profileImage === null) {
            $mediaType = $this->mediaTypeFacade->find(MediaTypeConstant::USER_PROFILE_IMAGE->value);

            $media = new Media;
            $media->setType($mediaType);
            $media->setOwner($user);
        }

        $maxImageWidth = $this->parameterService
            ->get('constraints.profile.image.maxWidth');

        $imageData = $this->resizeImageService->resizeBase64Image($imageData, $maxImageWidth, true);
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
        $this->entityManagerService->persistAndFlush($media);

        if ($profileImage === null) {
            $user->setProfileImage($media);
            $this->entityManagerService->flush();
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
    }
}
