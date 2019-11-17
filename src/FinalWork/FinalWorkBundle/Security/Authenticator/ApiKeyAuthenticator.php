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

namespace FinalWork\FinalWorkBundle\Security\Authenticator;

use Doctrine\ORM\NonUniqueResultException;
use FinalWork\FinalWorkBundle\Security\Provider\ApiKeyUserProvider;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\{
    TokenInterface,
    PreAuthenticatedToken
};
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface
{
    private const AUTH_KEY = 'X-AUTH-API-KEY';

    /**
     * @param Request $request
     * @param $providerKey
     * @return PreAuthenticatedToken
     */
    public function createToken(Request $request, $providerKey): PreAuthenticatedToken
    {
        return $this->getTokenByApiKey($this->getApiKeyByRequest($request));
    }

    /**
     * @param Request $request
     * @return string|null
     */
    private function getApiKeyByRequest(Request $request): ?string
    {
         return $request->headers->get(self::AUTH_KEY);
    }

    /**
     * @param $apiKey
     * @return PreAuthenticatedToken
     */
    private function getTokenByApiKey($apiKey): PreAuthenticatedToken
    {
        return new PreAuthenticatedToken('api', $apiKey, 'api');
    }

    /**
     * @param TokenInterface $token
     * @param UserProviderInterface $userProvider
     * @param $providerKey
     * @return PreAuthenticatedToken
     * @throws NonUniqueResultException
     */
    public function authenticateToken(
        TokenInterface $token,
        UserProviderInterface $userProvider,
        $providerKey
    ): PreAuthenticatedToken {
        if (!$userProvider instanceof ApiKeyUserProvider) {
            throw new InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of ApiUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $apiKey = $token->getCredentials();
        if (empty($apiKey)) {
            throw new CustomUserMessageAuthenticationException('Api key is empty.');
        }

        $user = $userProvider->loadUserByUsername($apiKey);

        return new PreAuthenticatedToken(
            $user,
            $apiKey,
            $providerKey,
            $user->getRoles()
        );
    }

    /**
     * @param TokenInterface $token
     * @param $providerKey
     * @return bool
     */
    public function supportsToken(TokenInterface $token, $providerKey): bool
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }
}