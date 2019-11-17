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

use DateTime;
use Exception;
use FOS\UserBundle\Event\{
    FormEvent,
    GetResponseUserEvent,
    FilterUserResponseEvent,
    GetResponseNullableUserEvent
};
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class ResettingController extends Controller
{
    /**
     * @return Response
     */
    public function requestAction(): Response
    {
        $this->get('final_work.seo_page')->setTitle('finalwork.page.login');

        return $this->render('@FOSUser/Resetting/request.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function sendEmailAction(Request $request): Response
    {
        $username = $request->request->get('username');

        /** @var $user UserInterface */
        $user = $this->get('fos_user.user_manager')
            ->findUserByUsernameOrEmail($username);

        if ($user === null) {
            return new RedirectResponse($this->generateUrl('fos_user_resetting_request', array('invalid' => true)));
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        /* Dispatch init event */
        $event = new GetResponseNullableUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if ($event->getResponse() !== null) {
            return $event->getResponse();
        }

        $ttl = $this->container
            ->getParameter('fos_user.resetting.retry_ttl');

        if ($user !== null && !$user->isPasswordRequestNonExpired($ttl)) {
            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_REQUEST, $event);

            if ($event->getResponse() !== null) {
                return $event->getResponse();
            }

            if ($user->getConfirmationToken() === null) {
                /** @var $tokenGenerator TokenGeneratorInterface */
                $tokenGenerator = $this->get('fos_user.util.token_generator');
                $user->setConfirmationToken($tokenGenerator->generateToken());
            }

            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);

            if ($event->getResponse() !== null) {
                return $event->getResponse();
            }

            $this->get('fos_user.mailer')->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new DateTime);
            $this->get('fos_user.user_manager')->updateUser($user);

            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);

            if ($event->getResponse() !== null) {
                return $event->getResponse();
            }
        }

        return new RedirectResponse($this->generateUrl('fos_user_resetting_check_email', ['username' => $username]));
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function checkEmailAction(Request $request): Response
    {
        $username = $request->query->get('username');

        if (empty($username)) {
            return new RedirectResponse($this->generateUrl('fos_user_resetting_request'));
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.login');

        return $this->render('@FOSUser/Resetting/check_email.html.twig', array(
            'tokenLifetime' => ceil($this->retryTtl / 3600),
        ));
    }

    /**
     * @param Request $request
     * @param string $token
     * @return RedirectResponse|Response
     */
    public function resetAction(Request $request, string $token): Response
    {
        $user = $this->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if ($user === null) {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_security_login'));
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->get('event_dispatcher')->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        if ($event->getResponse() !== null) {
            return $event->getResponse();
        }

        $form = $this->get('fos_user.resetting.form.factory')->createForm();
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $this->get('event_dispatcher')->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);
            $this->get('fos_user.user_manager')->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $this->get('event_dispatcher')->dispatch(
                FOSUserEvents::RESETTING_RESET_COMPLETED,
                new FilterUserResponseEvent($user, $request, $response)
            );

            return $response;
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.login');

        return $this->render('@FOSUser/Resetting/reset.html.twig', array(
            'token' => $token,
            'form' => $form->createView(),
        ));
    }
}
