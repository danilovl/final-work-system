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

namespace App\Tests\Unit\Infrastructure\Event\EventListener;

use App\Application\Service\SeoPageService;
use App\Infrastructure\Event\EventListener\RequestListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\{
    KernelEvents,
    KernelInterface,
    HttpKernelInterface
};

class RequestListenerTest extends TestCase
{
    private MockObject&SeoPageService $seoPageService;

    private RequestListener $listener;

    protected function setUp(): void
    {
        $this->seoPageService = $this->createMock(SeoPageService::class);

        $this->listener = new RequestListener($this->seoPageService);
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

        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestNotUser(): void
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

        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestNotMain(): void
    {
        $request = new Request(attributes: [
            'seo' => ['title' => 'test'],
        ]);

        $event = new RequestEvent(
            $this->createMock(KernelInterface::class),
            $request,
            HttpKernelInterface::SUB_REQUEST
        );

        $this->seoPageService
            ->expects($this->never())
            ->method('setTitle');

        $this->listener->onKernelRequest($event);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->listener::getSubscribedEvents();

        $this->assertEquals('onKernelRequest', $subscribedEvents[KernelEvents::REQUEST]);
    }
}
