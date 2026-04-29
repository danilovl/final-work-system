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

namespace App\Tests\Unit\Infrastructure\Event\EventListener;

use App\Infrastructure\Event\EventListener\KernelListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\{
    KernelEvents,
    HttpKernelInterface
};
use App\Tests\Mock\Unit\Infrastructure\Event\EventListener\{
    MockSourceEntity,
    MockControllerMethod,
    MockTargetEntity,
    MockTargetEmptyEntity
};

class KernelListenerTest extends TestCase
{
    private KernelListener $listener;

    private HttpKernelInterface $kernel;

    private Request $request;

    protected function setUp(): void
    {
        $this->listener = new KernelListener;
        $this->kernel = $this->createMock(HttpKernelInterface::class);
        $this->request = new Request;
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->listener::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::CONTROLLER_ARGUMENTS, $subscribedEvents);
        $this->assertEquals([['onKernelController', -1]], $subscribedEvents[KernelEvents::CONTROLLER_ARGUMENTS]);
    }

    public function testHandlesNonArrayController(): void
    {
        $event = new ControllerArgumentsEvent(
            $this->kernel,
            static function (): void {},
            [],
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->expectNotToPerformAssertions();

        $this->listener->onKernelController($event);
    }

    public function testHandlesNoAttributes(): void
    {
        $controller = [new MockControllerMethod, 'testEmptyMethod'];

        $event = new ControllerArgumentsEvent(
            $this->kernel,
            $controller,
            [],
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->expectNotToPerformAssertions();

        $this->listener->onKernelController($event);
    }

    public function testHandlesMissingEntities(): void
    {
        $controller = [new MockControllerMethod, 'testMethod'];

        $event = new ControllerArgumentsEvent(
            $this->kernel,
            $controller,
            [],
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->expectNotToPerformAssertions();

        $this->listener->onKernelController($event);
    }

    public function testThrowsOnNullRelatedEntity(): void
    {
        $controller = [new MockControllerMethod, 'testMethod'];
        $sourceEntity = new MockSourceEntity(1);
        $targetEntity = new MockTargetEntity(1);

        $event = new ControllerArgumentsEvent(
            $this->kernel,
            $controller,
            [$sourceEntity, $targetEntity],
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->expectException(BadRequestException::class);

        $message = sprintf(
            'Entity "%s" is not related to entity "%s": related entity is null.',
            MockSourceEntity::class,
            MockTargetEntity::class
        );
        $this->expectExceptionMessage($message);

        $this->listener->onKernelController($event);
    }

    public function testThrowsOnMismatchedIds(): void
    {
        $controller = [new MockControllerMethod, 'testMethod'];
        $targetEntity = new MockTargetEntity(2);
        $sourceEntity = new MockSourceEntity(1, new MockTargetEntity(1));

        $event = new ControllerArgumentsEvent(
            $this->kernel,
            $controller,
            [$sourceEntity, $targetEntity],
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->expectException(BadRequestException::class);

        $message = sprintf(
            'Entity "%s" is not related to entity "%s": IDs do not match (1 !== 2).',
            MockSourceEntity::class,
            MockTargetEntity::class
        );
        $this->expectExceptionMessage($message);

        $this->listener->onKernelController($event);
    }

    public function testThrowsOnMissingIds(): void
    {
        $controller = [new MockControllerMethod, 'testMethodNoId'];
        $targetEntity = new MockTargetEmptyEntity;
        $sourceEntity = new MockSourceEntity(1, targetEmptyEntity: new MockTargetEmptyEntity);

        $event = new ControllerArgumentsEvent(
            $this->kernel,
            $controller,
            [$sourceEntity, $targetEntity],
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Method "getId" not found.');

        $this->listener->onKernelController($event);
    }

    public function testHandlesMatchingIds(): void
    {
        $controller = [new MockControllerMethod, 'testMethod'];
        $targetEntity = new MockTargetEntity(1);
        $sourceEntity = new MockSourceEntity(1, new MockTargetEntity(1));

        $event = new ControllerArgumentsEvent(
            $this->kernel,
            $controller,
            [$sourceEntity, $targetEntity],
            $this->request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onKernelController($event);
        $this->expectNotToPerformAssertions();
    }
}
