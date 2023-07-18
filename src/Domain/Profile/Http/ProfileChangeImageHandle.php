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

namespace App\Domain\Profile\Http;

use App\Application\Constant\FlashTypeConstant;
use App\Application\Exception\RuntimeException;
use App\Application\Service\{
    EntityManagerService,
    RequestService,
    TwigRenderService};
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Facade\MediaTypeFacade;
use App\Domain\Media\Model\MediaModel;
use App\Domain\MediaMimeType\Entity\MediaMimeType;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\Profile\Form\ProfileMediaForm;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response};

readonly class ProfileChangeImageHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private TwigRenderService $twigRenderService,
        private MediaTypeFacade $mediaTypeFacade,
        private FormFactoryInterface $formFactory
    ) {}

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

                if ($mediaMimeType === null) {
                    throw new RuntimeException("FileMimeType don't exist");
                }

                $media = $profileImage ?? new Media;
                if ($profileImage === null) {
                    $mediaType = $this->mediaTypeFacade->find(MediaTypeConstant::USER_PROFILE_IMAGE->value);

                    $media = new Media;
                    $media->setType($mediaType);
                    $media->setOwner($user);
                }

                $media->setUploadMedia($uploadMedia);
                $this->entityManagerService->persistAndFlush($media);

                if ($profileImage === null) {
                    $user->setProfileImage($media);
                    $this->entityManagerService->flush();
                }

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
            }
        }

        return $this->twigRenderService->render('profile/edit_image.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
