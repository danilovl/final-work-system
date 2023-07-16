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

namespace App\Application\Security\Authenticator;

use App\Application\Service\EntityManagerService;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Service\UserService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\{
    AuthenticationException,
    CustomUserMessageAuthenticationException
};
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\{
    UserBadge,
    CsrfTokenBadge
};
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public function __construct(
        private readonly UserService $userService,
        private readonly UserFacade $userFacade,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly HttpUtils $httpUtils,
        private readonly HttpKernelInterface $httpKernel,
        private readonly EntityManagerService $entityManagerService
    ) {}

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'security_login' &&
            $request->isMethod(Request::METHOD_POST);
    }

    public function getCredentials(Request $request): array
    {
        $credentials = [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    public function getUser(array $credentials): callable
    {
        return function () use ($credentials): User {
            if (empty($credentials['username'])) {
                throw new CustomUserMessageAuthenticationException('Invalid credentials.');
            }

            $user = $this->userFacade->findOneByUsername($credentials['username']);
            if ($user === null) {
                throw new CustomUserMessageAuthenticationException('Invalid credentials.');
            }

            return $user;
        };
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $this->getCredentials($request);

        $userBadge = new UserBadge($credentials['username'], $this->getUser($credentials));
        $passwordCredentials = new PasswordCredentials($credentials['password']);
        $badges = [new CsrfTokenBadge('authenticate', $credentials['csrf_token'])];

        return new Passport($userBadge, $passwordCredentials, $badges);
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): RedirectResponse {
        $user = $this->userService->getUser();
        $user->updateLastLogin();

        $this->entityManagerService->flush();

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('homepage'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        $loginUrl = $this->urlGenerator->generate('security_login');

        return $this->httpUtils->createRedirectResponse($request, $loginUrl);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $loginUrl = $this->urlGenerator->generate('security_login');

        $subRequest = $this->httpUtils->createRequest($request, $loginUrl);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        }

        return $response;
    }
}
