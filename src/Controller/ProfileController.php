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

use App\Constant\MediaTypeConstant;
use App\Model\Media\MediaModel;
use App\Model\User\UserModel;
use Exception;
use App\Form\{
    ProfileFormType,
    ProfileMediaForm,
    ProfileChangePasswordFormType
};
use App\Entity\{
    Media,
    MediaType,
    MediaMimeType
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use RuntimeException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class ProfileController extends BaseController
{
    public function show(): Response
    {
        return $this->render('profile/show.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = $this->getUser();

        $userModel = UserModel::fromUser($user);
        $form = $this->createForm(ProfileFormType::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $refreshPage = false;
                if ($userModel->locale !== null && $userModel->locale !== $user->getLocale()) {
                    $refreshPage = true;
                }

                $this->get('app.factory.user')
                    ->flushFromModel($userModel, $user);

                $this->addFlash('success', $this->get('translator')->trans('app.flash.form.save.success', [], 'flashes'));

                if ($refreshPage) {
                    return $this->redirectToRoute('profile_edit', [
                        '_locale' => $user->getLocale()
                    ]);
                }
            } else {
                $this->addFlash('warning', $this->get('translator')->trans('app.flash.form.save.warning', [], 'flashes'));
                $this->addFlash('error', $this->get('translator')->trans('app.flash.form.save.error', [], 'flashes'));
            }
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function changeImage(Request $request): Response
    {
        $user = $this->getUser();
        $media = $this->getRepository(Media::class)->findOneBy([
            'owner' => $user,
            'type' => $this->getReference(MediaType::class, MediaTypeConstant::USER_PROFILE_IMAGE)
        ]);

        $mediaModel = new MediaModel;
        $form = $this->createForm(ProfileMediaForm::class, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $uploadMedia = $mediaModel->uploadMedia;
                $mimeType = $uploadMedia->getMimeType();

                $mediaMimeType = $this->getRepository(MediaMimeType::class)->findOneBy(['name' => $mimeType]);
                if ($mediaMimeType === null || empty($mediaMimeType)) {
                    throw new RuntimeException("FileMimeType don't exist");
                }

                if ($media === null) {
                    $media = new Media;
                }

                $media->setUploadMedia($uploadMedia);
                $media->setOwner($user);

                $this->flushEntity();

                $user->setProfileImage($media);
                $this->flushEntity();

                $this->addFlash('success', $this->get('translator')->trans('app.flash.form.create.success', [], 'flashes'));
            } else {
                $this->addFlash('error', $this->get('translator')->trans('app.flash.form.create.error', [], 'flashes'));
                $this->addFlash('warning', $this->get('translator')->trans('app.flash.form.create.warning', [], 'flashes'));
            }
        }

        return $this->render('profile/edit_image.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function deleteImage(Request $request): RedirectResponse
    {
        try {
            $this->removeEntity($this->getUser()->getProfileImage());
            $this->addFlash('success', $this->get('translator')->trans('app.flash.form.delete.success', [], 'flashes'));
        } catch (Exception $e) {
            $this->addFlash('error', $this->get('translator')->trans('app.flash.form.delete.error', [], 'flashes'));
            $this->addFlash('warning', $this->get('translator')->trans('app.flash.form.delete.warning', [], 'flashes'));
        }

        return $this->redirectToRoute('profile_show');
    }

    public function changePassword(Request $request): Response
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $userModel = UserModel::fromUser($user);
        $form = $this->createForm(ProfileChangePasswordFormType::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.password_updater')->hashPassword(
                    $form->get('plainPassword')->getData(),
                    $user,
                    $userModel
                );

                $this->get('app.factory.user')
                    ->flushFromModel($userModel, $user);

                $this->addFlash('success', $this->get('translator')->trans('app.flash.form.create.success', [], 'flashes'));
            } else {
                $this->addFlash('warning', $this->get('translator')->trans('app.flash.form.save.warning', [], 'flashes'));
                $this->addFlash('error', $this->get('translator')->trans('app.flash.form.save.error', [], 'flashes'));
            }
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
