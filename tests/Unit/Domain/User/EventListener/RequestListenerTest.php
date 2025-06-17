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
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\Service\EntityManagerService;
use App\Domain\User\Service\UserService;
use Danilovl\AsyncBundle\Service\AsyncService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\{
    KernelEvents,
    KernelInterface,
    HttpKernelInterface
};

class RequestListenerTest extends TestCase
{
    private MockObject&UserService $userService;

    private MockObject&EntityManagerService $entityManagerService;

    private AsyncService $asyncService;

    private RequestListener $listener;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->entityManagerService = $this->createMock(EntityManagerService::class);
        $this->asyncService = new AsyncService;

        $this->listener = new RequestListener(
            $this->userService,
            $this->entityManagerService,
            $this->asyncService
        );
    }

    public function testOnKernelRequest(): void
    {
        $event = new RequestEvent(
            $this->createMock(KernelInterface::class),
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
        $event = new RequestEvent(
            $this->createMock(KernelInterface::class),
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
        $event = new RequestEvent(
            $this->createMock(KernelInterface::class),
            new Request,
            HttpKernelInterface::SUB_REQUEST
        );

        $this->userService
            ->expects($this->never())
            ->method('getUserOrNull');

        $this->listener->onKernelRequest($event);
        $this->asyncService->call();
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->listener::getSubscribedEvents();

        $this->assertEquals('onKernelRequest', $subscribedEvents[KernelEvents::REQUEST]);
    }
}
