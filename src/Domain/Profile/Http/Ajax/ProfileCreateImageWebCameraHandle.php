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
use App\Application\Exception\RuntimeException;
use App\Application\Helper\FileHelper;
use App\Domain\MediaType\Entity\MediaType;
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

    public function __invoke(Request $request): JsonResponse
    {
        /** @var array $imageData */
        $imageData = json_decode($request->getContent(), true);
        $imageData = $imageData['imageData'] ?? null;

        $user = $this->userService->getUser();
        $profileImage = $user->getProfileImage();

        $media = $profileImage ?? new Media;
        if ($profileImage === null) {
            /** @var MediaType $mediaType */
            $mediaType = $this->mediaTypeFacade->find(MediaTypeConstant::USER_PROFILE_IMAGE->value);

            $media = new Media;
            $media->setType($mediaType);
            $media->setOwner($user);
        }

        $maxImageWidth = $this->parameterService
            ->getInt('constraints.profile.image.maxWidth');

        $imageData = $this->resizeImageService->resizeBase64Image($imageData, $maxImageWidth, true);
        if ($imageData === null) {
            throw new RuntimeException('Cannot resize image.');
        }

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
