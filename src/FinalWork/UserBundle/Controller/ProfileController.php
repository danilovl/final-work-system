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

namespace FinalWork\UserBundle\Controller;

use FinalWork\FinalWorkBundle\Constant\MediaTypeConstant;
use FinalWork\FinalWorkBundle\Entity\{
    Media,
    MediaType,
    MediaMimeType
};
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\UserBundle\Form\Type\ProfileMediaForm;
use FOS\UserBundle\Event\{
    FormEvent,
    GetResponseUserEvent,
    FilterUserResponseEvent
};
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\UserBundle\Model\{
    UserInterface,
    UserManagerInterface
};
use http\Exception\RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProfileController extends Controller
{
    /**
     * @return Response
     */
    public function showAction(): Response
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $this->get('final_work.seo_page')->setTitle('finalwork.page.profile');

        return $this->render('@FOSUser/Profile/show.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if ($event->getResponse() !== null) {
            return $event->getResponse();
        }

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var $userManager UserManagerInterface */
                $userManager = $this->get('fos_user.user_manager');

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

                $userManager->updateUser($user);

                $response = $event->getResponse();
                if ($response === null) {
                    $url = $this->generateUrl('fos_user_profile_edit');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }

            $this->get('session')->getFlashBag()->add('warning', $this->get('translator')->trans('finalwork.flash.form.save.warning', [], 'flashes'));
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('finalwork.flash.form.save.error', [], 'flashes'));
        }
        $this->get('final_work.seo_page')->setTitle('finalwork.page.profile_edit');

        return $this->render('@FOSUser/Profile/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function changeImageAction(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $em = $this->getDoctrine()->getManager();

        $media = $em->getRepository(Media::class)->findOneBy([
            'owner' => $user,
            'type' => $em->getReference(MediaType::class, MediaTypeConstant::USER_PROFILE_IMAGE)
        ]);

        if ($media === null) {
            $media = new Media;
            $media->setType($em->getReference(MediaType::class, MediaTypeConstant::USER_PROFILE_IMAGE));
        }

        $media->setName('profile image');
        $form = $this->createForm(ProfileMediaForm::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $uploadMedia = $media->getUploadMedia();
                $originalMediaName = $uploadMedia->getClientOriginalName();
                $originalMediaExtension = $uploadMedia->getClientOriginalExtension();
                $mimeType = $uploadMedia->getMimeType();
                $mediaSize = $uploadMedia->getSize();

                $mediaMimeType = $em->getRepository(MediaMimeType::class)->findOneBy(['name' => $mimeType]);
                if ($mediaMimeType === null || empty($mediaMimeType)) {
                    throw new RuntimeException("FileMimeType don't exist");
                }

                $mediaName = sha1(uniqid((string)mt_rand(), true)) . '.' . $mediaMimeType->getExtension();
                $media->setMediaName($mediaName);
                $media->setMimeType($mediaMimeType);
                $media->setOwner($user);
                $media->setOriginalMediaName($originalMediaName);
                $media->setOriginalExtension($originalMediaExtension);
                $media->setMediaSize($mediaSize);
                $user->setProfileImage($media);

                $em->persist($media);
                $em->flush();

                $this->addFlash('success', $this->get('translator')->trans('finalwork.flash.form.create.success', [], 'flashes'));
            } else {
                $this->addFlash('error', $this->get('translator')->trans('finalwork.flash.form.create.error', [], 'flashes'));
                $this->addFlash('warning', $this->get('translator')->trans('finalwork.flash.form.create.warning', [], 'flashes'));
            }
        }
        $this->get('final_work.seo_page')->setTitle('finalwork.page.profile_edit');

        return $this->render('@FOSUser/Profile/edit_image.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
