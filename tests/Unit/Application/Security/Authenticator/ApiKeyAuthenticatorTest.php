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

namespace App\Tests\Unit\Application\Security\Authenticator;

use App\Application\Model\Security\ApiKeyCredentialModel;
use App\Application\Security\Authenticator\ApiKeyAuthenticator;
use App\Application\Service\EntityManagerService;
use App\Domain\ApiUser\Entity\ApiUser;
use App\Domain\ApiUser\Facade\ApiUserFacade;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Service\UserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiKeyAuthenticatorTest extends TestCase
{
    private readonly Request $request;
    private readonly ApiKeyAuthenticator $authenticator;
    private readonly UserService $userService;
    private readonly UserFacade $userFacade;
    private readonly ApiUserFacade $apiUserFacade;

    protected function setUp(): void
    {
        $this->request = new Request(request: [
            'username' => 'username',
            'password' => 'password',
        ]);
        $this->request->headers->set(ApiKeyAuthenticator::AUTH_KEY, 'AUTH_KEY');
        $this->request->headers->set(ApiKeyAuthenticator::AUTH_USER_TOKEN_KEY, 'AUTH_USER_TOKEN_KEY');
        $this->request->headers->set(ApiKeyAuthenticator::AUTH_USER_USERNAME, 'AUTH_USER_USERNAME');

        $this->userService = $this->createMock(UserService::class);
        $this->apiUserFacade = $this->createMock(ApiUserFacade::class);
        $this->userFacade = $this->createMock(UserFacade::class);
        $entityManagerService = $this->createMock(EntityManagerService::class);

        $this->authenticator = new ApiKeyAuthenticator(
            $this->userService,
            $this->apiUserFacade,
            $this->userFacade,
            $entityManagerService
        );
    }

    public function testSupports(): void
    {
        $this->apiUserFacade
            ->expects($this->once())
            ->method('findByApiKey')
            ->willReturn(new ApiUser);

        $this->assertTrue($this->authenticator->supports($this->request));

        $this->request->headers->remove(ApiKeyAuthenticator::AUTH_KEY);
        $this->assertFalse($this->authenticator->supports($this->request));
    }

    public function testGetCredentials(): void
    {
        $credentials = $this->authenticator->getCredentials($this->request);

        $this->assertInstanceOf(ApiKeyCredentialModel::class, $credentials);

        $this->assertSame('AUTH_KEY', $credentials->authToken);
        $this->assertSame('AUTH_USER_TOKEN_KEY', $credentials->authUserToken);
        $this->assertSame('AUTH_USER_USERNAME', $credentials->authUserUsername);
        $this->assertSame('username', $credentials->username);
        $this->assertSame('password', $credentials->password);
    }

    public function testAuthenticateByAuthUserToken(): void
    {
        $user = new User;

        $this->userFacade
            ->expects($this->once())
            ->method('findOneByToken')
            ->willReturn($user);

        $result = $this->authenticator->authenticate($this->request);

        $this->assertSame($user, $result->getUser());
    }

    public function testAuthenticateByUsername(): void
    {
        $user = new User;
        $this->request->headers->remove(ApiKeyAuthenticator::AUTH_USER_TOKEN_KEY);

        $this->userFacade
            ->expects($this->once())
            ->method('findOneByUsername')
            ->willReturn($user);

        $result = $this->authenticator->authenticate($this->request);

        $this->assertSame($user, $result->getUser());
    }

    public function testOnAuthenticationSuccess(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(new User);

        $result = $this->authenticator->onAuthenticationSuccess(
            $this->request,
            $token,
            'main'
        );

        $this->assertNull($result);
    }

    public function testOnAuthenticationFailure(): void
    {
        $result = $this->authenticator->onAuthenticationFailure(
            $this->request,
            new AuthenticationException
        );

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertSame('{"message":"An authentication exception occurred."}', $result->getContent());
    }
}
