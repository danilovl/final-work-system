<?php declare(strict_types=1);

namespace App\Tests\Unit\Application\Middleware\EventCalendar\Ajax;

use App\Application\Constant\DateFormatConstant;
use App\Application\Middleware\EventCalendar\Ajax\EditMiddleware;
use App\Infrastructure\Service\TranslatorService;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EditMiddlewareTest extends TestCase
{
    private EditMiddleware $editMiddleware;

    protected function setUp(): void
    {
        $translatorService = $this->createStub(TranslatorService::class);
        $translatorService->method('trans')->willReturn('content');

        $this->editMiddleware = new EditMiddleware($translatorService);
    }

    public function testAttributeHandleSuccess(): void
    {
        $request = new Request;
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME->value));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATE_TIME->value));

        $controllerEvent = $this->getMockControllerEvent($request);
        $result = $this->editMiddleware->__invoke($controllerEvent);

        $this->assertTrue($result);
    }

    public function testHandleFailsDateForm(): void
    {
        $request = new Request;
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE->value));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATABASE->value));

        $controllerEvent = $this->getMockControllerEvent($request);
        $this->editMiddleware->__invoke($controllerEvent);

        $this->assertInstanceOf(JsonResponse::class, $controllerEvent->getController()());
    }

    public function testAttributeHandleFailsDateStart(): void
    {
        $request = new Request;
        $request->request->set('start', (new DateTime)->format(DateFormatConstant::DATE_TIME->value));
        $request->request->set('end', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME->value));

        $controllerEvent = $this->getMockControllerEvent($request);
        $this->editMiddleware->__invoke($controllerEvent);

        $this->assertInstanceOf(JsonResponse::class, $controllerEvent->getController()());
    }

    private function getMockControllerEvent(Request $request): ControllerEvent
    {
        return new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            static fn (): bool => true,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
