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

namespace App\Tests\Unit\Domain\User\EventListener;

use App\Application\Constant\LocaleConstant;
use App\Domain\User\Entity\User;
use App\Domain\User\EventListener\RedirectToPreferredLocaleListener;
use App\Domain\User\Service\UserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    RedirectResponse
};
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\{
    KernelEvents,
    KernelInterface,
    HttpKernelInterface
};
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UnexpectedValueException;

class RedirectToPreferredLocaleListenerTest extends TestCase
{
    private RequestEvent $event;

    protected function setUp(): void
    {
        $this->event = new RequestEvent(
            $this->createMock(KernelInterface::class),
            new Request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }

    public function testOnKernelRequestRedirectEmptyLocales(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $userService = $this->createMock(UserService::class);

        $user = new User;
        $user->setLocale(LocaleConstant::ISO_RU->value);

        $this->expectException(UnexpectedValueException::class);

        new RedirectToPreferredLocaleListener(
            $urlGenerator,
            $userService,
            '',
            LocaleConstant::ISO_EN->value
        );
    }

    public function testOnKernelRequestRedirectDefaultLocale(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $userService = $this->createMock(UserService::class);

        $user = new User;
        $user->setLocale(LocaleConstant::ISO_RU->value);

        $this->expectException(UnexpectedValueException::class);

        new RedirectToPreferredLocaleListener(
            $urlGenerator,
            $userService,
            implode('|', LocaleConstant::values()),
            'locale'
        );
    }

    public function testOnKernelRequestRedirect(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->exactly(1))
            ->method('generate')
            ->willReturn('url');

        $user = new User;
        $user->setLocale(LocaleConstant::ISO_RU->value);

        $userService = $this->createMock(UserService::class);
        $userService->expects($this->exactly(1))
            ->method('getUserOrNull')
            ->willReturn($user);

        $listener = new RedirectToPreferredLocaleListener(
            $urlGenerator,
            $userService,
            implode('|', LocaleConstant::values()),
            LocaleConstant::ISO_EN->value
        );

        $listener->onKernelRequest($this->event);
        $result = $this->event->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testOnKernelRequestNoRedirect(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->exactly(0))
            ->method('generate')
            ->willReturn('url');

        $user = new User;
        $user->setLocale(LocaleConstant::ISO_EN->value);

        $userService = $this->createMock(UserService::class);
        $userService->expects($this->exactly(1))
            ->method('getUserOrNull')
            ->willReturn($user);

        $listener = new RedirectToPreferredLocaleListener(
            $urlGenerator,
            $userService,
            implode('|', LocaleConstant::values()),
            LocaleConstant::ISO_EN->value
        );

        $listener->onKernelRequest($this->event);
        $result = $this->event->getResponse();

        $this->assertNull($result);
    }

    public function testOnKernelRequestNotMain(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->never())
            ->method('generate');

        $user = new User;
        $user->setLocale(LocaleConstant::ISO_EN->value);

        $userService = $this->createMock(UserService::class);
        $userService->expects($this->never())
            ->method('getUserOrNull');

        $listener = new RedirectToPreferredLocaleListener(
            $urlGenerator,
            $userService,
            implode('|', LocaleConstant::values()),
            LocaleConstant::ISO_EN->value
        );

        $event = new RequestEvent(
            $this->createMock(KernelInterface::class),
            new Request,
            HttpKernelInterface::SUB_REQUEST
        );

        $listener->onKernelRequest($event);
    }

    public function testOnKernelRequestReferer(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->never())
            ->method('generate');

        $user = new User;
        $user->setLocale(LocaleConstant::ISO_EN->value);

        $userService = $this->createMock(UserService::class);
        $userService->expects($this->never())
            ->method('getUserOrNull');

        $listener = new RedirectToPreferredLocaleListener(
            $urlGenerator,
            $userService,
            implode('|', LocaleConstant::values()),
            LocaleConstant::ISO_EN->value
        );

        $request = new Request;
        $request->headers->set('referer', 'http://:example.com');

        $event = new RequestEvent(
            $this->createMock(KernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $listener->onKernelRequest($event);
    }

    public function testGetSubscribedEvents(): void
    {
        $listenerEvents = RedirectToPreferredLocaleListener::getSubscribedEvents();

        $this->assertEquals('onKernelRequest', $listenerEvents[KernelEvents::REQUEST]);
    }
}
