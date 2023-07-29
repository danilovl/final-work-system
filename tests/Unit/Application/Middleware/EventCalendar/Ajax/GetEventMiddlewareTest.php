<?php declare(strict_types=1);

namespace App\Tests\Unit\Application\Middleware\EventCalendar\Ajax;

use App\Application\Constant\DateFormatConstant;
use App\Application\Exception\AjaxRuntimeException;
use App\Application\Middleware\EventCalendar\Ajax\GetEventMiddleware;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class GetEventMiddlewareTest extends TestCase
{
    public function testAttributeHandleSuccess(): void
    {
        $request = new Request;
        $request->attributes->set('type', 'type');
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME->value));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATE_TIME->value));

        $this->assertEquals(
            true,
            GetEventMiddleware::handle($request)
        );
    }

    public function testHandleFailsDateForm(): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectExceptionMessage('Bad format date');

        $request = new Request;
        $request->attributes->set('type', 'type');
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE->value));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATABASE->value));

        GetEventMiddleware::handle($request);
    }

    public function testAttributeHandleFailsType(): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectExceptionMessage('Empty type');

        $request = new Request;
        $request->attributes->set('type', null);
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE->value));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATABASE->value));

        $this->assertEquals(
            true,
            GetEventMiddleware::handle($request)
        );
    }

    public function testAttributeHandleFailsDateStart(): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectExceptionMessage('StartDate must be less then endDate');

        $request = new Request;
        $request->attributes->set('type', 'type');
        $request->request->set('start', (new DateTime)->format(DateFormatConstant::DATE_TIME->value));
        $request->request->set('end', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME->value));

        $this->assertEquals(
            true,
            GetEventMiddleware::handle($request)
        );
    }
}
