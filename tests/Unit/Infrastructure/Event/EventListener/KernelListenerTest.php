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
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Throwable;
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
    MockMultiRelationEntity,
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
        $this->kernel = $this->createStub(HttpKernelInterface::class);
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
            kernel: $this->kernel,
            controller: static function (): void {},
            arguments: [],
            request: $this->request,
            requestType: HttpKernelInterface::MAIN_REQUEST
        );

        $this->expectNotToPerformAssertions();

        $this->listener->onKernelController($event);
    }

    public function testHandlesNoAttributes(): void
    {
        $controller = [new MockControllerMethod, 'testEmptyMethod'];

        $event = new ControllerArgumentsEvent(
            kernel: $this->kernel,
            controller: $controller,
            arguments: [],
            request: $this->request,
            requestType: HttpKernelInterface::MAIN_REQUEST
        );

        $this->expectNotToPerformAssertions();

        $this->listener->onKernelController($event);
    }

    public function testHandlesMissingEntities(): void
    {
        $controller = [new MockControllerMethod, 'testMethod'];

        $event = new ControllerArgumentsEvent(
            kernel: $this->kernel,
            controller: $controller,
            arguments: [],
            request: $this->request,
            requestType: HttpKernelInterface::MAIN_REQUEST
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
            kernel: $this->kernel,
            controller: $controller,
            arguments: [$sourceEntity, $targetEntity],
            request: $this->request,
            requestType: HttpKernelInterface::MAIN_REQUEST
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
            kernel: $this->kernel,
            controller: $controller,
            arguments: [$sourceEntity, $targetEntity],
            request: $this->request,
            requestType: HttpKernelInterface::MAIN_REQUEST
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
            kernel: $this->kernel,
            controller: $controller,
            arguments: [$sourceEntity, $targetEntity],
            request: $this->request,
            requestType: HttpKernelInterface::MAIN_REQUEST
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
            kernel: $this->kernel,
            controller: $controller,
            arguments: [$sourceEntity, $targetEntity],
            request: $this->request,
            requestType: HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onKernelController($event);
        $this->expectNotToPerformAssertions();
    }

    #[DataProvider('providePassesValidRelationCases')]
    public function testPassesValidRelation(callable $controller, object $source, object $target): void
    {
        $event = $this->createEvent(
            controller: $controller,
            source: $source,
            target: $target
        );

        $this->listener->onKernelController($event);
        $this->expectNotToPerformAssertions();
    }

    /**
     * @param class-string<Throwable> $exception
     */
    #[DataProvider('provideThrowsOnInvalidRelationCases')]
    public function testThrowsOnInvalidRelation(
        callable $controller,
        object $source,
        object $target,
        string $exception,
        string $message
    ): void {
        $event = $this->createEvent(
            controller: $controller,
            source: $source,
            target: $target
        );

        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        $this->listener->onKernelController($event);
    }

    public static function providePassesValidRelationCases(): Generator
    {
        yield 'property from attribute' => [
            'controller' => [new MockControllerMethod, 'testMethodSecondProperty'],
            'source' => new MockMultiRelationEntity(
                first: new MockTargetEntity(2),
                second: new MockTargetEntity(1)
            ),
            'target' => new MockTargetEntity(1)
        ];

        yield 'target in collection' => [
            'controller' => [new MockControllerMethod, 'testMethodCollection'],
            'source' => new MockMultiRelationEntity(
                targets: new ArrayCollection([new MockTargetEntity(1), new MockTargetEntity(2)])
            ),
            'target' => new MockTargetEntity(2)
        ];
    }

    public static function provideThrowsOnInvalidRelationCases(): Generator
    {
        yield 'several properties of target type' => [
            'controller' => [new MockControllerMethod, 'testMethodSeveralProperties'],
            'source' => new MockMultiRelationEntity,
            'target' => new MockTargetEntity(1),
            'exception' => LogicException::class,
            'message' => 'has several properties of type'
        ];

        yield 'property from attribute does not match' => [
            'controller' => [new MockControllerMethod, 'testMethodSecondProperty'],
            'source' => new MockMultiRelationEntity(
                first: new MockTargetEntity(1),
                second: new MockTargetEntity(2)
            ),
            'target' => new MockTargetEntity(1),
            'exception' => BadRequestException::class,
            'message' => 'IDs do not match (2 !== 1)'
        ];

        yield 'property from attribute is null' => [
            'controller' => [new MockControllerMethod, 'testMethodSecondProperty'],
            'source' => new MockMultiRelationEntity(first: new MockTargetEntity(1)),
            'target' => new MockTargetEntity(1),
            'exception' => BadRequestException::class,
            'message' => 'related entity is null'
        ];

        yield 'target not in collection' => [
            'controller' => [new MockControllerMethod, 'testMethodCollection'],
            'source' => new MockMultiRelationEntity(
                targets: new ArrayCollection([new MockTargetEntity(1)])
            ),
            'target' => new MockTargetEntity(2),
            'exception' => BadRequestException::class,
            'message' => 'related entity is null'
        ];

        yield 'empty collection' => [
            'controller' => [new MockControllerMethod, 'testMethodCollection'],
            'source' => new MockMultiRelationEntity(targets: new ArrayCollection),
            'target' => new MockTargetEntity(1),
            'exception' => BadRequestException::class,
            'message' => 'related entity is null'
        ];

        yield 'uninitialized property' => [
            'controller' => [new MockControllerMethod, 'testMethodUninitialized'],
            'source' => new MockMultiRelationEntity,
            'target' => new MockTargetEntity(1),
            'exception' => BadRequestException::class,
            'message' => 'related entity is null'
        ];

        yield 'unknown property' => [
            'controller' => [new MockControllerMethod, 'testMethodUnknownProperty'],
            'source' => new MockMultiRelationEntity,
            'target' => new MockTargetEntity(1),
            'exception' => LogicException::class,
            'message' => 'Property "unknown" not found'
        ];

        yield 'no property of target type' => [
            'controller' => [new MockControllerMethod, 'testMethodNoPropertyOfType'],
            'source' => new MockTargetEmptyEntity,
            'target' => new MockTargetEntity(1),
            'exception' => LogicException::class,
            'message' => 'has no property of type'
        ];
    }

    private function createEvent(callable $controller, object $source, object $target): ControllerArgumentsEvent
    {
        return new ControllerArgumentsEvent(
            kernel: $this->kernel,
            controller: $controller,
            arguments: [$source, $target],
            request: $this->request,
            requestType: HttpKernelInterface::MAIN_REQUEST
        );
    }
}
