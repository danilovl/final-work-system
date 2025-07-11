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

namespace App\Tests\Unit\Domain\User\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\User\Entity\User;
use App\Domain\User\EventDispatcher\GenericEvent\UserGenericEvent;
use App\Domain\User\EventDispatcher\UserEventDispatcher;
use App\Infrastructure\Service\EventDispatcherService;
use Danilovl\AsyncBundle\Service\AsyncService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use PHPUnit\Framework\TestCase;

class UserEventDispatcherTest extends TestCase
{
    private MockObject&EventDispatcherService $eventDispatcher;

    private AsyncService $asyncService;

    private UserEventDispatcher $userEventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherService::class);
        $this->asyncService = new AsyncService;
        $this->userEventDispatcher = new UserEventDispatcher($this->eventDispatcher, $this->asyncService);
    }

    #[DataProvider('dispatchProvider')]
    public function testDispatch(string $method, int $exactly, array $expectEvents, array $expectNames): void
    {
        $user = $this->createMock(User::class);

        $this->eventDispatcher
            ->expects($this->exactly($exactly))
            ->method('dispatch')
            ->will($this->createReturnCallback($expectEvents, $expectNames));

        $this->userEventDispatcher->{$method}($user, $user);
        $this->asyncService->call();
    }

    private function createReturnCallback(array $expectEvents, array $expectNames): ReturnCallback
    {
        return $this->returnCallback(function (UserGenericEvent $event, string $eventName) use ($expectEvents, $expectNames): UserGenericEvent {
            $this->assertTrue(in_array(get_class($event), $expectEvents, true));
            $this->assertTrue(in_array($eventName, $expectNames, true));

            return $event;
        });
    }

    public static function dispatchProvider(): Generator
    {
        yield ['onUserCreate', 1,
            [
                UserGenericEvent::class
            ],
            [
                Events::USER_CREATE
            ]
        ];

        yield ['onUserEdit', 1,
            [
                UserGenericEvent::class
            ],
            [
                Events::USER_EDIT
            ]
        ];
    }
}
