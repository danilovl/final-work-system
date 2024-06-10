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
use Danilovl\AsyncBundle\Service\AsyncService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PHPUnit\Framework\TestCase;

class VersionEventDispatcherServiceTest extends TestCase
{
    private readonly EventDispatcherInterface $eventDispatcher;
    private readonly AsyncService $asyncService;
    private readonly VersionEventDispatcherService $versionEventDispatcherService;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->asyncService = new AsyncService;
        $this->versionEventDispatcherService = new VersionEventDispatcherService($this->eventDispatcher, $this->asyncService);
    }

    #[DataProvider('dispatchProvider')]
    public function testDispatch(string $method, int $exactly, array $expectEvents, array $expectNames): void
    {
        $media = $this->createMock(Media::class);

        $this->eventDispatcher
            ->expects($this->exactly($exactly))
            ->method('dispatch')
            ->will($this->createReturnCallback($expectEvents, $expectNames));

        $this->versionEventDispatcherService->{$method}($media);
        $this->asyncService->call();
    }

    private function createReturnCallback(array $expectEvents, array $expectNames): ReturnCallback
    {
        return $this->returnCallback(function (VersionGenericEvent $event, string $eventName) use ($expectEvents, $expectNames): VersionGenericEvent {
            $this->assertTrue(in_array(get_class($event), $expectEvents, true));
            $this->assertTrue(in_array($eventName, $expectNames, true));

            return $event;
        });
    }

    public static function dispatchProvider(): Generator
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
