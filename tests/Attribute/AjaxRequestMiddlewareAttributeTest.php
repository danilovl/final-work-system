<?php declare(strict_types=1);

namespace App\Tests\Attribute;

use App\Attribute\AjaxRequestMiddlewareAttribute;

use App\Constant\DateFormatConstant;
use App\Exception\AjaxRuntimeException;
use App\Middleware\EventCalendar\Ajax\GetEventMiddleware;
use DateTime;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

class AjaxRequestMiddlewareAttributeTest extends TestCase
{
    /**
     * @dataProvider classMethodProvider
     */
    public function testAttributeInstance(object $object, string $method): void
    {
        $attribute = $this->getAttribute($object, $method);

        $this->assertEquals(AjaxRequestMiddlewareAttribute::class, get_class($attribute));
    }

    /**
     * @dataProvider classMethodProvider
     */
    public function testClassInstance(
        object $object,
        string $method,
        string $class
    ): void {
        $attribute = $this->getAttribute($object, $method);

        $this->assertEquals($class, $attribute->class);
    }

    /**
     * @dataProvider classMethodProvider
     */
    public function testAttributeHandleSuccess(object $object, string $method): void
    {
        $attribute = $this->getAttribute($object, $method);

        $request = new Request;
        $request->request->set('type', 'type');
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATE_TIME));

        $this->assertEquals(
            true,
            call_user_func([$attribute->class, 'handle'], $request)
        );
    }

    /**
     * @dataProvider classMethodProvider
     */
    public function testAttributeHandleFailsDateForm(object $object, string $method): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectErrorMessage('Bad format date');

        $attribute = $this->getAttribute($object, $method);

        $request = new Request;
        $request->request->set('type', 'type');
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATABASE));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATABASE));

        call_user_func([$attribute->class, 'handle'], $request);
    }

    /**
     * @dataProvider classMethodProvider
     */
    public function testAttributeHandleFailsType(object $object, string $method): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectErrorMessage('Empty type');

        $attribute = $this->getAttribute($object, $method);

        $request = new Request;
        $request->request->set('type', null);
        $request->request->set('start', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME));
        $request->request->set('end', (new DateTime)->format(DateFormatConstant::DATE_TIME));

        call_user_func([$attribute->class, 'handle'], $request);
    }

    /**
     * @dataProvider classMethodProvider
     */
    public function testAttributeHandleFailsDateStart(object $object, string $method): void
    {
        $this->expectException(AjaxRuntimeException::class);
        $this->expectErrorMessage('StartDate must be less then endDate');

        $attribute = $this->getAttribute($object, $method);

        $request = new Request;
        $request->request->set('type', 'type');
        $request->request->set('start', (new DateTime)->format(DateFormatConstant::DATE_TIME));
        $request->request->set('end', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME));

        call_user_func([$attribute->class, 'handle'], $request);
    }

    public function classMethodProvider(): Generator
    {
        yield
        [
            new class {
                #[AjaxRequestMiddlewareAttribute([
                    'class' => 'App\Middleware\EventCalendar\Ajax\GetEventMiddleware'
                ])]
                public function getEvent()
                {
                }
            },
            'getEvent',
            GetEventMiddleware::class
        ];
    }

    private function getAttribute(object $object, string $method): ?AjaxRequestMiddlewareAttribute
    {
        $attributes = (new ReflectionClass($object))
            ->getMethod($method)
            ->getAttributes(AjaxRequestMiddlewareAttribute::class);

        return $attributes[0]?->newInstance();
    }
}
