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

namespace App\Security\Authenticator;

use App\Model\User\UserFacade;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\{
    AuthenticationException,
    CustomUserMessageAuthenticationException
};
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\{
    UserBadge,
    CsrfTokenBadge
};
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\{
    Passport,
    PassportInterface
};
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private UserFacade $userFacade,
        private UrlGeneratorInterface $urlGenerator,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

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
        return function () use ($credentials) {
            if (empty($credentials['username'])) {
                throw new CustomUserMessageAuthenticationException('Username is empty.');
            }

            $user = $this->userFacade->findUserByUsername($credentials['username']);
            if ($user === null) {
                throw new CustomUserMessageAuthenticationException('Username could not be found.');
            }

            return $user;
        };
    }

    public function authenticate(Request $request): PassportInterface
    {
        $credentials = $this->getCredentials($request);

        $userBadge = new UserBadge($credentials['username'], $this->getUser($credentials));
        $passwordCredentials = new PasswordCredentials($credentials['password']);
        $badges = [new CsrfTokenBadge('login', $credentials['csrf_token'])];

        return new Passport($userBadge, $passwordCredentials, $badges);
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): RedirectResponse {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('homepage'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return  null;
    }
}
