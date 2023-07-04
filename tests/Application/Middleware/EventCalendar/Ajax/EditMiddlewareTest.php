<?php declare(strict_types=1);

namespace App\Tests\Application\Middleware\EventCalendar\Ajax;

use App\Application\Constant\DateFormatConstant;
use App\Application\Exception\AjaxRuntimeException;
use App\Application\Middleware\EventCalendar\Ajax\EditMiddleware;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class EditMiddlewareTest extends TestCase
{
    public function testAttributeHandleSuccess(): void
    {
        $request = new Request;
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME->value));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATE_TIME->value));

        $this->assertEquals(
            true,
            EditMiddleware::handle($request)
        );
    }

    public function testHandleFailsDateForm(): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectExceptionMessage('Bad format date');

        $request = new Request;
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE->value));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATABASE->value));

        EditMiddleware::handle($request);
    }

    public function testAttributeHandleFailsDateStart(): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectExceptionMessage('StartDate must be less then endDate');

        $request = new Request;
        $request->request->set('start', (new DateTime)->format(DateFormatConstant::DATE_TIME->value));
        $request->request->set('end', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME->value));

        $this->assertEquals(
            true,
            EditMiddleware::handle($request)
        );
    }
}
