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
use App\Application\Security\Authenticator\ApiAuthenticator;
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
use Symfony\Component\Security\Core\Exception\{
    AuthenticationException,
    CustomUserMessageAuthenticationException
};
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class ApiAuthenticatorTest extends TestCase
{
    private Request $request;
    private ApiAuthenticator $authenticator;
    private UserService $userService;
    private UserFacade $userFacade;
    private ApiUserFacade $apiUserFacade;

    protected function setUp(): void
    {
        $this->request = new Request;
        $this->request->headers->set(ApiAuthenticator::AUTH_KEY, 'AUTH_KEY');
        $this->request->headers->set(ApiAuthenticator::AUTH_USER_TOKEN_KEY, 'AUTH_USER_TOKEN_KEY');
        $this->request->headers->set(ApiAuthenticator::AUTH_USER_USERNAME, 'AUTH_USER_USERNAME');

        $this->userService = $this->createMock(UserService::class);
        $this->apiUserFacade = $this->createMock(ApiUserFacade::class);
        $this->userFacade = $this->createMock(UserFacade::class);
        $entityManagerService = $this->createMock(EntityManagerService::class);

        $this->authenticator = new ApiAuthenticator(
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

        $this->request->headers->remove(ApiAuthenticator::AUTH_KEY);
        $this->assertFalse($this->authenticator->supports($this->request));
    }

    public function testGetCredentials(): void
    {
        $credentials = $this->authenticator->getCredentials($this->request);

        $this->assertInstanceOf(ApiKeyCredentialModel::class, $credentials);

        $this->assertSame('AUTH_KEY', $credentials->authToken);
        $this->assertSame('AUTH_USER_TOKEN_KEY', $credentials->authUserToken);
        $this->assertSame('AUTH_USER_USERNAME', $credentials->authUserUsername);
    }

    public function testAuthenticateByAuthUserTokenSuccess(): void
    {
        $user = new User;

        $this->userFacade
            ->expects($this->once())
            ->method('findOneByToken')
            ->willReturn($user);

        $result = $this->authenticator->authenticate($this->request);

        $this->assertSame($user, $result->getUser());
    }

    public function testAuthenticateByAuthUserTokenFailed(): void
    {
        $this->userFacade
            ->expects($this->once())
            ->method('findOneByToken')
            ->willReturn(null);

        $this->expectException(CustomUserMessageAuthenticationException::class);

        $result = $this->authenticator->authenticate($this->request);
        $result->getUser();
    }

    public function testAuthenticateByAuthToken(): void
    {
        $result = $this->authenticator->authenticate($this->request);

        $this->assertInstanceOf(Passport::class, $result);
    }

    public function testOnAuthenticationSuccess(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $this->userService
            ->expects($this->once())
            ->method('getUserOrNull')
            ->willReturn(new User);

        $result = $this->authenticator->onAuthenticationSuccess(
            $this->request,
            $token,
            'main'
        );

        $this->assertNull($result);
    }

    public function testOnAuthenticationSuccessNotUser(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $this->userService
            ->expects($this->once())
            ->method('getUserOrNull')
            ->willReturn(null);

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
