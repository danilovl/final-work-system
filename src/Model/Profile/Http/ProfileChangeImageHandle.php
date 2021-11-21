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

namespace App\Model\Profile\Http;

use App\Constant\FlashTypeConstant;
use App\Constant\MediaTypeConstant;
use App\Model\Media\Entity\Media;
use App\Model\MediaMimeType\Entity\MediaMimeType;
use App\Model\Profile\Form\ProfileMediaForm;
use App\Exception\RuntimeException;
use App\Model\Media\Facade\MediaTypeFacade;
use App\Model\Media\MediaModel;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService,
    TwigRenderService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ProfileChangeImageHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private TwigRenderService $twigRenderService,
        private MediaTypeFacade $mediaTypeFacade,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();
        $profileImage = $user->getProfileImage();

        $mediaModel = new MediaModel;
        $form = $this->formFactory
            ->create(ProfileMediaForm::class, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $uploadMedia = $mediaModel->uploadMedia;
                $mimeType = $uploadMedia->getMimeType();

                $mediaMimeType = $this->entityManagerService
                    ->getRepository(MediaMimeType::class)
                    ->findOneBy(['name' => $mimeType]);

                if ($mediaMimeType === null || empty($mediaMimeType)) {
                    throw new RuntimeException("FileMimeType don't exist");
                }

                $media = $profileImage ?? new Media;
                if ($profileImage === null) {
                    $mediaType = $this->mediaTypeFacade->find(MediaTypeConstant::USER_PROFILE_IMAGE);

                    $media = new Media;
                    $media->setType($mediaType);
                    $media->setOwner($user);
                }

                $media->setUploadMedia($uploadMedia);
                $this->entityManagerService->persistAndFlush($media);

                if ($profileImage === null) {
                    $user->setProfileImage($media);
                    $this->entityManagerService->flush($user);
                }

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            }
        }

        return $this->twigRenderService->render('profile/edit_image.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
