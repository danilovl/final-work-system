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

namespace FinalWork\UserBundle\Controller;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class SecurityController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request): Response
    {
        /** @var $session Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif ($session !== null && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null;
        }

        $lastUsername = $session === null ? '' : $session->get($lastUsernameKey);

        $tokenManager = $this->get('security.csrf.token_manager');
        $csrfToken = $tokenManager
            ? $tokenManager->getToken('authenticate')->getValue()
            : null;

        $this->get('final_work.seo_page')->setTitle('finalwork.page.login');

        return $this->renderLogin(array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ));
    }

    /**
     * @return void
     */
    public function checkAction(): void
    {
        throw new RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @return void
     */
    public function logoutAction(): void
    {
        throw new RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    /**
     * @param array $data
     * @return Response
     */
    protected function renderLogin(array $data): Response
    {
        return $this->render('@FOSUser/Security/login.html.twig', $data);
    }
}
