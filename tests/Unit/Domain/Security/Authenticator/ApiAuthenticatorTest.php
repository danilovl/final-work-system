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

namespace App\Tests\Unit\Domain\Security\Authenticator;

use App\Domain\ApiUser\Entity\ApiUser;
use App\Domain\ApiUser\Facade\ApiUserFacade;
use App\Domain\Security\Authenticator\ApiAuthenticator;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\EntityManagerService;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
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

    private Stub&UserService $userService;

    protected function setUp(): void
    {
        $this->request = new Request;
        $this->request->headers->set(ApiAuthenticator::AUTH_KEY, 'AUTH_KEY');
        $this->request->headers->set(ApiAuthenticator::AUTH_USER_TOKEN_KEY, 'AUTH_USER_TOKEN_KEY');
        $this->request->headers->set(ApiAuthenticator::AUTH_USER_USERNAME, 'AUTH_USER_USERNAME');

        $this->userService = $this->createStub(UserService::class);
        /** @var Stub&ApiUserFacade $apiUserFacade */
        $apiUserFacade = $this->createStub(ApiUserFacade::class);
        /** @var Stub&UserFacade $userFacade */
        $userFacade = $this->createStub(UserFacade::class);
        /** @var Stub&EntityManagerService $entityManagerService */
        $entityManagerService = $this->createStub(EntityManagerService::class);

        $apiUserFacade
            ->method('findByApiKey')
            ->willReturnCallback(static function (string $key): ?ApiUser {
                return match ($key) {
                    'AUTH_KEY' => new ApiUser,
                    default => null
                };
            });

        $userFacade
            ->method('findByToken')
            ->willReturnCallback(static function (string $username, string $token): ?User {
                return match ([$username, $token]) {
                    ['AUTH_USER_USERNAME', 'AUTH_USER_TOKEN_KEY'] => new User,
                    default => null
                };
            });

        $this->authenticator = new ApiAuthenticator(
            $this->userService,
            $apiUserFacade,
            $userFacade,
            $entityManagerService
        );
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->authenticator->supports($this->request));

        $this->request->headers->remove(ApiAuthenticator::AUTH_KEY);
        $this->assertFalse($this->authenticator->supports($this->request));
    }

    public function testGetCredentials(): void
    {
        $credentials = $this->authenticator->getCredentials($this->request);

        $this->assertSame('AUTH_KEY', $credentials->authToken);
        $this->assertSame('AUTH_USER_TOKEN_KEY', $credentials->authUserToken);
        $this->assertSame('AUTH_USER_USERNAME', $credentials->authUserUsername);
    }

    public function testAuthenticateByAuthUserTokenSuccess(): void
    {
        $result = $this->authenticator->authenticate($this->request);

        $this->assertInstanceOf(User::class, $result->getUser());
    }

    public function testAuthenticateByAuthUserTokenFailed(): void
    {
        $this->request->headers->set(ApiAuthenticator::AUTH_USER_TOKEN_KEY, 'invalid');

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
        /** @var Stub&TokenInterface $token */
        $token = $this->createStub(TokenInterface::class);

        $this->userService
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
        /** @var Stub&TokenInterface $token */
        $token = $this->createStub(TokenInterface::class);

        $this->userService
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

        $this->assertSame('{"message":"An authentication exception occurred."}', $result->getContent());
    }
}
