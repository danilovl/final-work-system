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

namespace App\Tests\Application\Attribute;

use App\Application\Attribute\AjaxRequestMiddlewareAttribute;
use App\Application\Constant\DateFormatConstant;
use App\Application\Exception\AjaxRuntimeException;
use App\Application\Middleware\EventCalendar\Ajax\GetEventMiddleware;
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
        $request->attributes->set('type', 'type');
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
        $this->expectExceptionMessage('Bad format date');

        $attribute = $this->getAttribute($object, $method);

        $request = new Request;
        $request->attributes->set('type', 'type');
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
        $this->expectExceptionMessage('Empty type');

        $attribute = $this->getAttribute($object, $method);

        $request = new Request;
        $request->attributes->set('type', null);
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
        $this->expectExceptionMessage('StartDate must be less then endDate');

        $attribute = $this->getAttribute($object, $method);

        $request = new Request;
        $request->attributes->set('type', 'type');
        $request->request->set('start', (new DateTime)->format(DateFormatConstant::DATE_TIME));
        $request->request->set('end', (new DateTime('now -1 minute'))->format(DateFormatConstant::DATE_TIME));

        call_user_func([$attribute->class, 'handle'], $request);
    }

    public function classMethodProvider(): Generator
    {
        yield
        [
            new class {
                #[AjaxRequestMiddlewareAttribute(
                    class: GetEventMiddleware::class
                )]
                public function getEvent(): void
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
