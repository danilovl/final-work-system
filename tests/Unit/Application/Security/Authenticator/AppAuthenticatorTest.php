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

use App\Application\Security\Authenticator\AppAuthenticator;
use App\Application\Service\EntityManagerService;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Service\UserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\{
    AuthenticationException,
    CustomUserMessageAuthenticationException
};
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class AppAuthenticatorTest extends TestCase
{
    private Request $request;
    private UserService $userService;
    private AppAuthenticator $authenticator;
    private UrlGeneratorInterface $urlGenerator;
    private HttpUtils $httpUtils;
    private HttpKernelInterface $httpKernel;
    private UserFacade $userFacade;

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

        $this->userService = $this->createMock(UserService::class);
        $this->userFacade = $this->createMock(UserFacade::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->httpUtils = $this->createMock(HttpUtils::class);
        $this->httpKernel = $this->createMock(HttpKernelInterface::class);
        $entityManagerService = $this->createMock(EntityManagerService::class);

        $this->authenticator = new AppAuthenticator(
            $this->userService,
            $this->userFacade,
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
        $this->userFacade
            ->expects($this->once())
            ->method('findOneByUsername')
            ->willReturn(new User);

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
        $this->userFacade
            ->expects($this->once())
            ->method('findOneByUsername')
            ->willReturn(null);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $callback = $this->authenticator->getUser(['username' => 'username']);
        $callback();
    }

    public function testAuthenticate(): void
    {
        $user = new User;

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

        $this->urlGenerator
            ->expects($this->once())
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
        $token = $this->createMock(TokenInterface::class);

        $this->userService
            ->expects($this->once())
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
            ->expects($this->once())
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
            ->expects($this->once())
            ->method('generate')
            ->willReturn('url');

        $this->httpKernel
            ->expects($this->once())
            ->method('handle')
            ->willReturn(new Response);

        $result = $this->authenticator->start($this->request);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $result->getStatusCode());
    }
}
