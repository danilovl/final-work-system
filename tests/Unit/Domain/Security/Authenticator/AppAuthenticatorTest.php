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

use App\Domain\Security\Authenticator\AppAuthenticator;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\EntityManagerService;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request,
    Response
};
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\{
    AuthenticationException,
    CustomUserMessageAuthenticationException
};
use Symfony\Component\Security\Http\{
    HttpUtils,
    SecurityRequestAttributes
};

class AppAuthenticatorTest extends TestCase
{
    private Request $request;

    private Stub&UserService $userService;

    private Stub&UrlGeneratorInterface $urlGenerator;

    private Stub&HttpUtils $httpUtils;

    private Stub&HttpKernelInterface $httpKernel;

    private AppAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->request = new Request(
            request: [
                '_username' => 'username',
                '_password' => 'password',
                '_csrf_token' => 'csrf_token'
            ],
            attributes: [
                '_route' => 'security_login',
            ]
        );
        $this->request->setSession(new Session);

        $this->userService = $this->createStub(UserService::class);
        /** @var Stub&UserFacade $userFacade */
        $userFacade = $this->createStub(UserFacade::class);
        $this->urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $this->httpUtils = $this->createStub(HttpUtils::class);
        $this->httpKernel = $this->createStub(HttpKernelInterface::class);
        /** @var Stub&EntityManagerService $entityManagerService */
        $entityManagerService = $this->createStub(EntityManagerService::class);

        $userFacade
            ->method('findByUsername')
            ->willReturnCallback(static function (string $username): ?User {
                return match ($username) {
                    'username' => new User,
                    default => null
                };
            });

        $this->authenticator = new AppAuthenticator(
            $this->userService,
            $userFacade,
            $this->urlGenerator,
            $this->httpUtils,
            $this->httpKernel,
            $entityManagerService
        );
    }

    public function testSupports(): void
    {
        $this->request->setMethod(Request::METHOD_GET);
        $this->assertFalse($this->authenticator->supports($this->request));

        $this->request->setMethod(Request::METHOD_POST);
        $this->assertTrue($this->authenticator->supports($this->request));
    }

    public function testGetCredentials(): void
    {
        $credentials = $this->authenticator->getCredentials($this->request);

        $expectedCredentials = [
            'username' => 'username',
            'password' => 'password',
            'csrf_token' => 'csrf_token'
        ];
        $this->assertEquals($expectedCredentials, $credentials);

        $expectedSession = $this->request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME);
        $this->assertEquals($expectedSession, 'username');
    }

    public function testGetUserSuccess(): void
    {
        $callback = $this->authenticator->getUser(['username' => 'username']);
        $user = $callback();

        $this->assertInstanceOf(User::class, $user);
    }

    public function testGetUserFailed(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $callback = $this->authenticator->getUser([]);
        $callback();
    }

    public function testGetUserFacadeFailed(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $callback = $this->authenticator->getUser(['username' => 'usernameNull']);
        $callback();
    }

    public function testAuthenticate(): void
    {
        $result = $this->authenticator->authenticate($this->request);

        $this->assertInstanceOf(User::class, $result->getUser());
    }

    public function testOnAuthenticationSuccess(): void
    {
        /** @var Stub&TokenInterface $token */
        $token = $this->createStub(TokenInterface::class);

        $this->userService
            ->method('getUser')
            ->willReturn(new User);

        $this->urlGenerator
            ->method('generate')
            ->willReturn('url');

        $result = $this->authenticator->onAuthenticationSuccess(
            $this->request,
            $token,
            'main'
        );

        $this->assertSame('url', $result->getTargetUrl());
    }

    public function testOnAuthenticationRedirectSuccess(): void
    {
        /** @var Stub&TokenInterface $token */
        $token = $this->createStub(TokenInterface::class);

        $this->userService
            ->method('getUser')
            ->willReturn(new User);

        $this->request->getSession()->set('_security.main.target_path', 'target_path');

        $result = $this->authenticator->onAuthenticationSuccess(
            $this->request,
            $token,
            'main'
        );

        $this->assertSame('target_path', $result->getTargetUrl());
    }

    public function testOnAuthenticationFailure(): void
    {
        $this->httpUtils
            ->method('createRedirectResponse')
            ->willReturn(new RedirectResponse('url'));

        $result = $this->authenticator->onAuthenticationFailure(
            $this->request,
            new AuthenticationException
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame('url', $result->getTargetUrl());
    }

    public function testStart(): void
    {
        $this->urlGenerator
            ->method('generate')
            ->willReturn('url');

        $this->httpKernel
            ->method('handle')
            ->willReturn(new Response);

        $result = $this->authenticator->start($this->request);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $result->getStatusCode());
    }
}
