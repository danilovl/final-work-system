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

use App\Domain\User\Entity\User;
use App\Domain\User\EventListener\RequestListener;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\EntityManagerService;
use Danilovl\AsyncBundle\Service\AsyncService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\{
    HttpKernelInterface,
    KernelEvents,
    KernelInterface
};
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListenerTest extends TestCase
{
    private UserService $userService;

    private EntityManagerService $entityManagerService;

    private AsyncService $asyncService;

    private RequestListener $listener;

    protected function setUp(): void
    {
        $this->userService = $this->createStub(UserService::class);
        $this->entityManagerService = $this->createStub(EntityManagerService::class);
        $this->asyncService = new AsyncService;

        $this->listener = new RequestListener(
            $this->userService,
            $this->entityManagerService,
            $this->asyncService
        );
    }

    public function testOnKernelRequest(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->entityManagerService = $this->createMock(EntityManagerService::class);
        $this->listener = new RequestListener(
            $this->userService,
            $this->entityManagerService,
            $this->asyncService
        );

        $event = new RequestEvent(
            $this->createStub(KernelInterface::class),
            new Request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->userService
            ->expects($this->once())
            ->method('getUserOrNull')
            ->willReturn(new User);

        $this->entityManagerService
            ->expects($this->once())
            ->method('flush');

        $this->listener->onKernelRequest($event);
        $this->asyncService->call();
    }

    public function testOnKernelRequestNotUser(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->entityManagerService = $this->createMock(EntityManagerService::class);
        $this->listener = new RequestListener(
            $this->userService,
            $this->entityManagerService,
            $this->asyncService
        );

        $event = new RequestEvent(
            $this->createStub(KernelInterface::class),
            new Request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->userService
            ->expects($this->once())
            ->method('getUserOrNull')
            ->willReturn(null);

        $this->entityManagerService
            ->expects($this->never())
            ->method('flush');

        $this->listener->onKernelRequest($event);
        $this->asyncService->call();
    }

    public function testOnKernelRequestNotMain(): void
    {
        $this->expectNotToPerformAssertions();

        $event = new RequestEvent(
            $this->createStub(KernelInterface::class),
            new Request,
            HttpKernelInterface::SUB_REQUEST
        );

        $this->listener->onKernelRequest($event);
        $this->asyncService->call();
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->listener::getSubscribedEvents();

        $this->assertEquals('onKernelRequest', $subscribedEvents[KernelEvents::REQUEST]);
    }
}
