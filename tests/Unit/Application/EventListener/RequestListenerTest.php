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

namespace App\Tests\Unit\Application\EventListener;

use App\Application\EventListener\RequestListener;
use App\Domain\User\Entity\User;
use App\Application\Service\{
    SeoPageService,
    EntityManagerService
};
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
    private readonly UserService $userService;
    private readonly EntityManagerService $entityManagerService;
    private readonly SeoPageService $seoPageService;
    private readonly AsyncService $asyncService;
    private readonly RequestListener $listener;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->entityManagerService = $this->createMock(EntityManagerService::class);
        $this->seoPageService = $this->createMock(SeoPageService::class);
        $this->asyncService = new AsyncService;

        $this->listener = new RequestListener(
            $this->userService,
            $this->entityManagerService,
            $this->seoPageService,
            $this->asyncService
        );
    }

    public function testOnKernelRequest(): void
    {
        $request = new Request(attributes: [
            'seo' => ['title' => 'test'],
        ]);

        $event = new RequestEvent(
            $this->createMock(KernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->seoPageService
            ->expects($this->once())
            ->method('setTitle');

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(new User);

        $this->entityManagerService
            ->expects($this->once())
            ->method('flush');

        $this->listener->onKernelRequest($event);
        $this->asyncService->call();
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->listener::getSubscribedEvents();

        $this->assertEquals('onKernelRequest', $subscribedEvents[KernelEvents::REQUEST]);
    }
}
