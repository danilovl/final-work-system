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

namespace App\Tests\Unit\Domain\Version\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\Media\Entity\Media;
use App\Domain\Version\EventDispatcher\GenericEvent\VersionGenericEvent;
use App\Domain\Version\EventDispatcher\VersionEventDispatcherService;
use App\Infrastructure\Service\EventDispatcherService;
use Closure;
use Danilovl\AsyncBundle\Service\AsyncService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VersionEventDispatcherServiceTest extends TestCase
{
    private MockObject&EventDispatcherService $eventDispatcher;

    private AsyncService $asyncService;

    private VersionEventDispatcherService $versionEventDispatcherService;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherService::class);
        $this->asyncService = new AsyncService;
        $this->versionEventDispatcherService = new VersionEventDispatcherService($this->eventDispatcher, $this->asyncService);
    }

    #[DataProvider('provideDispatchCases')]
    public function testDispatch(string $method, int $exactly, array $expectEvents, array $expectNames): void
    {
        $media = $this->createMock(Media::class);

        $this->eventDispatcher
            ->expects($this->exactly($exactly))
            ->method('dispatch')
            ->willReturnCallback($this->createReturnCallback($expectEvents, $expectNames));

        $this->versionEventDispatcherService->{$method}($media);
        $this->asyncService->call();
    }

    private function createReturnCallback(array $expectEvents, array $expectNames): Closure
    {
        return function (VersionGenericEvent $event, string $eventName) use ($expectEvents, $expectNames): VersionGenericEvent {
            $this->assertTrue(in_array(get_class($event), $expectEvents, true));
            $this->assertTrue(in_array($eventName, $expectNames, true));

            return $event;
        };
    }

    public static function provideDispatchCases(): Generator
    {
        yield ['onVersionCreate', 1,
            [
                VersionGenericEvent::class
            ],
            [
                Events::WORK_VERSION_CREATE
            ]
        ];

        yield ['onVersionEdit', 1,
            [
                VersionGenericEvent::class
            ],
            [
                Events::WORK_VERSION_EDIT
            ]
        ];
    }
}
