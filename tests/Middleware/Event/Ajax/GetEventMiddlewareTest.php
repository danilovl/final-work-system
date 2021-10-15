<?php declare(strict_types=1);

namespace App\Tests\Middleware\Event\Ajax;

use App\Constant\DateFormatConstant;
use App\Exception\AjaxRuntimeException;
use App\Middleware\Event\Ajax\GetEventMiddleware;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class GetEventMiddlewareTest extends TestCase
{
    public function testAttributeHandleSuccess(): void
    {
        $request = new Request;
        $request->attributes->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME));
        $request->attributes->set('end', (new DateTime)->format(DateFormatConstant::DATE_TIME));

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
        $request->attributes->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE));
        $request->attributes->set('end', (new DateTime)->format(DateFormatConstant::DATABASE));

        GetEventMiddleware::handle($request);
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
            GetEventMiddleware::handle($request)
        );
    }
}
