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

use App\Application\Constant\UserRoleConstant;
use App\Application\Model\Security\ApiKeyCredentialModel;
use App\Application\Security\Authenticator\Credential\CustomApiKeyCredentials;
use App\Application\Service\{
    UserService,
    EntityManagerService
};
use App\Domain\ApiUser\Entity\ApiUser;
use App\Domain\ApiUser\Facade\ApiUserFacade;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
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
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\{
    Passport,
    SelfValidatingPassport
};

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private const AUTH_KEY = 'X-AUTH-API-KEY';
    private const AUTH_USER_TOKEN_KEY = 'X-AUTH-USER-TOKEN';
    private const AUTH_USER_USERNAME = 'X-AUTH-USER-USERNAME';

    public function __construct(
        private readonly UserService $userService,
        private readonly ApiUserFacade $apiUserFacade,
        private readonly UserFacade $userFacade,
        private readonly EntityManagerService $entityManagerService
    ) {
    }

    public function supports(Request $request): bool
    {
        $authKey = $request->headers->get(self::AUTH_KEY);
        if ($authKey === null) {
            return false;
        }

        $apiUser = $this->apiUserFacade->findByApiKey($authKey);

        return $apiUser !== null;
    }

    public function getCredentials(Request $request): ApiKeyCredentialModel
    {
        return new ApiKeyCredentialModel(
            authToken: $request->headers->get(self::AUTH_KEY),
            authUserToken: $request->headers->get(self::AUTH_USER_TOKEN_KEY),
            authUserUsername: $request->headers->get(self::AUTH_USER_USERNAME),
            username: $request->request->get('username'),
            password: $request->request->get('password')
        );
    }

    public function authenticate(Request $request): Passport
    {
        $apiKeyCredential = $this->getCredentials($request);

        if (!empty($apiKeyCredential->authUserToken)) {
            return $this->authenticateUserToken($apiKeyCredential);
        }

        if (!empty($apiKeyCredential->username) && !empty($apiKeyCredential->password)) {
            return $this->authenticateUsername($apiKeyCredential);
        }

        return $this->authenticateAuthToken($apiKeyCredential);
    }

    private function authenticateUserToken(ApiKeyCredentialModel $apiKeyCredentialModel): Passport
    {
        $username = $apiKeyCredentialModel->authUserUsername;

        $userBadge = new UserBadge($username, $this->getUserByToken($apiKeyCredentialModel));
        $passwordCredentials = new CustomApiKeyCredentials;

        return new Passport($userBadge, $passwordCredentials);
    }

    private function authenticateUsername(ApiKeyCredentialModel $apiKeyCredentialModel): Passport
    {
        $username = $apiKeyCredentialModel->username;
        $password = $apiKeyCredentialModel->password;

        $userBadge = new UserBadge($username, $this->getUser($username));
        $passwordCredentials = new PasswordCredentials($password);

        return new Passport($userBadge, $passwordCredentials);
    }

    private function authenticateAuthToken(ApiKeyCredentialModel $apiKeyCredentialModel): Passport
    {
        $authToken = $apiKeyCredentialModel->authToken;

        return new SelfValidatingPassport(new UserBadge($authToken));
    }

    public function getUser(string $username): callable
    {
        return function () use ($username): User {
            $user = $this->userFacade->findOneByUsername($username);
            if ($user === null) {
                throw new CustomUserMessageAuthenticationException('User could not be found.');
            }

            $user->addAdditionRole(UserRoleConstant::API);

            return $user;
        };
    }

    public function getApiUser(string $authToken): callable
    {
        return function () use ($authToken): ?ApiUser {
            if ($authToken === null) {
                throw new CustomUserMessageAuthenticationException('ApiKey could not be found.');
            }

            $user = $this->apiUserFacade->findByApiKey($authToken);
            if ($user === null) {
                throw new CustomUserMessageAuthenticationException('ApiUser could not be found.');
            }

            return $user;
        };
    }

    public function getUserByToken(ApiKeyCredentialModel $apiKeyCredentialModel): callable
    {
        return function () use ($apiKeyCredentialModel): User {
            $username = $apiKeyCredentialModel->authUserUsername;
            $userToken = $apiKeyCredentialModel->authUserToken;

            $user = $this->userFacade->findOneByToken($username, $userToken);
            if ($user === null) {
                throw new CustomUserMessageAuthenticationException('Username by token could not be found.');
            }

            $user->addAdditionRole(UserRoleConstant::API);

            return $user;
        };
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        $user = $this->userService->getUser();
        if ($user === null) {
            return null;
        }

        $user->updateLastLogin();
        $this->entityManagerService->flush($user);

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
