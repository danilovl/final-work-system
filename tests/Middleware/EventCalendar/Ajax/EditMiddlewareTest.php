<?php

namespace App\Tests\Middleware\EventCalendar\Ajax;

use App\Constant\DateFormatConstant;
use App\Exception\AjaxRuntimeException;
use App\Middleware\EventCalendar\Ajax\EditMiddleware;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class EditMiddlewareTest extends TestCase
{
    public function testAttributeHandleSuccess(): void
    {
        $request = new Request;
        $request->attributes->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME));
        $request->attributes->set('end', (new DateTime)->format(DateFormatConstant::DATE_TIME));

        $this->assertEquals(
            true,
            EditMiddleware::handle($request)
        );
    }

    public function testHandleFailsDateForm(): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectErrorMessage('Bad format date');

        $request = new Request;
        $request->attributes->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE));
        $request->attributes->set('end', (new DateTime)->format(DateFormatConstant::DATABASE));

        EditMiddleware::handle($request);
    }

    public function testAttributeHandleFailsDateStart(): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectErrorMessage('StartDate must be less then endDate');

        $request = new Request;
        $request->attributes->set('start', (new DateTime)->format(DateFormatConstant::DATE_TIME));
        $request->attributes->set('end', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME));

        $this->assertEquals(
            true,
            EditMiddleware::handle($request)
        );
    }
}
