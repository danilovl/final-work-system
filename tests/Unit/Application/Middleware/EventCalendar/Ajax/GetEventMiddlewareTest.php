<?php declare(strict_types=1);

namespace App\Tests\Unit\Application\Middleware\EventCalendar\Ajax;

use App\Application\Constant\DateFormatConstant;
use App\Application\Middleware\EventCalendar\Ajax\GetEventMiddleware;
use App\Application\Service\TranslatorService;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class GetEventMiddlewareTest extends TestCase
{
    private GetEventMiddleware $getEventMiddleware;

    protected function setUp(): void
    {
        $translatorService = $this->createMock(TranslatorService::class);
        $translatorService->expects($this->any())
            ->method('trans')
            ->willReturn('content');

        $this->getEventMiddleware = new GetEventMiddleware($translatorService);
    }

    public function testAttributeHandleSuccess(): void
    {
        $request = new Request;
        $request->attributes->set('type', 'type');
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME->value));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATE_TIME->value));

        $controllerEvent = $this->getMockControllerEvent($request);
        $result = $this->getEventMiddleware->__invoke($controllerEvent);

        $this->assertTrue($result);
    }

    public function testHandleFailsDateForm(): void
    {
        $request = new Request;
        $request->attributes->set('type', 'type');
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE->value));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATABASE->value));

        $controllerEvent = $this->getMockControllerEvent($request);
        $this->getEventMiddleware->__invoke($controllerEvent);

        $this->assertInstanceOf(JsonResponse::class, $controllerEvent->getController()());
    }

    public function testAttributeHandleFailsType(): void
    {
        $request = new Request;
        $request->attributes->set('type', null);
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE->value));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATABASE->value));

        $controllerEvent = $this->getMockControllerEvent($request);
        $this->getEventMiddleware->__invoke($controllerEvent);

        $this->assertInstanceOf(JsonResponse::class, $controllerEvent->getController()());
    }

    public function testAttributeHandleFailsDateStart(): void
    {
        $request = new Request;
        $request->attributes->set('type', 'type');
        $request->request->set('start', (new DateTime)->format(DateFormatConstant::DATE_TIME->value));
        $request->request->set('end', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME->value));

        $controllerEvent = $this->getMockControllerEvent($request);
        $this->getEventMiddleware->__invoke($controllerEvent);

        $this->assertInstanceOf(JsonResponse::class, $controllerEvent->getController()());
    }

    private function getMockControllerEvent(Request $request): ControllerEvent
    {
        return new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            static fn (): bool => true,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
