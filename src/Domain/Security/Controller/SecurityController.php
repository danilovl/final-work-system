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

namespace App\Domain\Security\Controller;

use App\Infrastructure\Service\{
    RequestService,
    TwigRenderService
};
use App\Domain\User\Service\UserService;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

readonly class SecurityController
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private UserService $userService,
        private AuthenticationUtils $authenticationUtils
    ) {}

    public function login(): Response
    {
        if ($this->userService->getUserOrNull()) {
            return $this->requestService->redirectToRoute('homepage');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->twigRenderService->renderToResponse('application/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
