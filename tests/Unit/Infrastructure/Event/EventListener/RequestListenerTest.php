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

use App\Infrastructure\Event\EventListener\RequestListener;
use App\Infrastructure\Service\SeoPageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\{
    HttpKernelInterface,
    KernelEvents,
    KernelInterface
};

class RequestListenerTest extends TestCase
{
    private SeoPageService $seoPageService;

    private RequestListener $listener;

    protected function setUp(): void
    {
        $this->seoPageService = $this->createStub(SeoPageService::class);

        $this->listener = new RequestListener($this->seoPageService);
    }

    public function testOnKernelRequest(): void
    {
        $this->seoPageService = $this->createMock(SeoPageService::class);
        $this->listener = new RequestListener($this->seoPageService);

        $request = new Request(attributes: [
            '_seo' => ['title' => 'test'],
        ]);

        $event = new RequestEvent(
            $this->createStub(KernelInterface::class),
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
        $this->seoPageService = $this->createMock(SeoPageService::class);
        $this->listener = new RequestListener($this->seoPageService);

        $request = new Request(attributes: [
            '_seo' => ['title' => 'test'],
        ]);

        $event = new RequestEvent(
            $this->createStub(KernelInterface::class),
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
        $this->seoPageService = $this->createMock(SeoPageService::class);
        $this->listener = new RequestListener($this->seoPageService);

        $request = new Request(attributes: [
            '_seo' => ['title' => 'test'],
        ]);

        $event = new RequestEvent(
            $this->createStub(KernelInterface::class),
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
        $this->seoPageService = $this->createStub(SeoPageService::class);
        $this->listener = new RequestListener($this->seoPageService);

        $subscribedEvents = $this->listener::getSubscribedEvents();

        $this->assertEquals('onKernelRequest', $subscribedEvents[KernelEvents::REQUEST]);
    }
}
