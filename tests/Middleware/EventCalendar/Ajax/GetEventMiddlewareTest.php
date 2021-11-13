<?php declare(strict_types=1);

namespace App\Tests\Middleware\EventCalendar\Ajax;

use App\Constant\DateFormatConstant;
use App\Exception\AjaxRuntimeException;
use App\Middleware\EventCalendar\Ajax\GetEventMiddleware;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class GetEventMiddlewareTest extends TestCase
{
    public function testAttributeHandleSuccess(): void
    {
        $request = new Request;
        $request->attributes->set('type', 'type');
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATE_TIME));

        $this->assertEquals(
            true,
            GetEventMiddleware::handle($request)
        );
    }

    public function testHandleFailsDateForm(): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectErrorMessage('Bad format date');

        $request = new Request;
        $request->attributes->set('type', 'type');
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATABASE));

        GetEventMiddleware::handle($request);
    }

    public function testAttributeHandleFailsType(): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectErrorMessage('Empty type');

        $request = new Request;
        $request->attributes->set('type', null);
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATABASE));

        $this->assertEquals(
            true,
            GetEventMiddleware::handle($request)
        );
    }

    public function testAttributeHandleFailsDateStart(): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectErrorMessage('StartDate must be less then endDate');

        $request = new Request;
        $request->attributes->set('type', 'type');
        $request->request->set('start', (new DateTime)->format(DateFormatConstant::DATE_TIME));
        $request->request->set('end', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME));

        $this->assertEquals(
            true,
            GetEventMiddleware::handle($request)
        );
    }
}
