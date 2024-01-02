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

namespace App\Tests\Unit\Application\EventListener\Middleware;

use App\Application\EventListener\Middleware\AjaxRequestListener;
use App\Application\Service\TranslatorService;
use App\Tests\Helper\Controller\EventListener\TestController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\{
    KernelInterface,
    HttpKernelInterface
};

class AjaxRequestListenerTest extends TestCase
{
    public function testOnKernelControllerSuccess(): void
    {
        $translatorService = $this->createMock(TranslatorService::class);
        $translatorService->expects($this->exactly(0))
            ->method('trans')
            ->willReturn('content');

        $listener = new AjaxRequestListener($translatorService);
        $request = new Request;
        $request->setMethod(Request::METHOD_POST);

        $event = new ControllerEvent(
            $this->createMock(KernelInterface::class),
            [new TestController, 'index'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $listener->onKernelController($event);

        $this->assertTrue(true);
    }

    public function testOnKernelControllerFailedCallback(): void
    {
        $translatorService = $this->createMock(TranslatorService::class);
        $translatorService->expects($this->never())
            ->method('trans')
            ->willReturn('content');

        $listener = new AjaxRequestListener($translatorService);
        $request = new Request;
        $request->setMethod(Request::METHOD_POST);

        $event = new ControllerEvent(
            $this->createMock(KernelInterface::class),
            static function (): void {},
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $listener->onKernelController($event);
     }

    public function testOnKernelControllerFailedGet(): void
    {
        $translatorService = $this->createMock(TranslatorService::class);
        $translatorService->expects($this->never())
            ->method('trans')
            ->willReturn('content');

        $listener = new AjaxRequestListener($translatorService);
        $request = new Request;
        $request->setMethod(Request::METHOD_GET);

        $event = new ControllerEvent(
            $this->createMock(KernelInterface::class),
            [new TestController, 'error'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $listener->onKernelController($event);
    }

    public function testOnKernelControllerFailed(): void
    {
        $translatorService = $this->createMock(TranslatorService::class);
        $translatorService->expects($this->any())
            ->method('trans')
            ->willReturn('content');

        $listener = new AjaxRequestListener($translatorService);
        $request = new Request;
        $request->setMethod(Request::METHOD_POST);

        $event = new ControllerEvent(
            $this->createMock(KernelInterface::class),
            [new TestController, 'error'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $listener->onKernelController($event);
        $result = $event->getController()();

        $this->assertInstanceOf(JsonResponse::class, $result);
    }
}
