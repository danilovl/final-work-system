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

use FinalWork\SonataUserBundle\Entity\User;
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
use InvalidArgumentException;
use LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ChangePasswordController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function changePasswordAction(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.change_password.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var $userManager UserManagerInterface */
                $userManager = $this->get('fos_user.user_manager');

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_profile_show');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }

            $this->get('session')->getFlashBag()->add('warning', $this->get('translator')->trans('finalwork.flash.form.save.warning', array(), 'flashes'));
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('finalwork.flash.form.save.error', array(), 'flashes'));
        }
        $this->get('final_work.seo_page')->setTitle('finalwork.page.profile_edit');

        return $this->render('@FOSUser/ChangePassword/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
