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

use App\Model\ApiUser\ApiUserFacade;
use App\Security\Authenticator\Credential\CustomApiKeyCredentials;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\{
    AuthenticationException,
    CustomUserMessageAuthenticationException
};
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\{
    Passport,
    PassportInterface
};

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private const CREDENTIALS_KEY = 'api_key';
    private const AUTH_KEY = 'X-AUTH-API-KEY';

    public function __construct(private ApiUserFacade $apiUserFacade)
    {
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has(self::AUTH_KEY);
    }

    public function authenticate(Request $request): PassportInterface
    {
        $credentials = $this->getCredentials($request);

        $userBadge = new UserBadge('api_user', $this->getUser($credentials));
        $passwordCredentials = new CustomApiKeyCredentials;

        return new Passport($userBadge, $passwordCredentials);
    }

    public function getCredentials(Request $request): array
    {
        return [
            self::CREDENTIALS_KEY => $request->headers->get(self::AUTH_KEY)
        ];
    }

    public function getUser(array $credentials): callable
    {
        return function () use ($credentials) {
            $apiKey = $credentials[self::CREDENTIALS_KEY];
            if ($apiKey === null) {
                throw new CustomUserMessageAuthenticationException('Apikey could not be found.');
            }

            return $this->apiUserFacade->findByApiKey($apiKey);
        };
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $firewallName
    ): ?Response {
        return null;
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): JsonResponse {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }
}