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

namespace App\Tests\Unit\Domain\SystemEvent\EventSubscriber;

use App\Application\Service\EntityManagerService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BaseSystemEventSubscriber extends TestCase
{
    protected readonly EventDispatcher $dispatcher;
    protected readonly EntityManagerService $entityManagerService;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher;
        $this->entityManagerService = $this->createMock(EntityManagerService::class);
    }

    #[DataProvider('subscribedEvents')]
    public function testInitialState(string $eventKey): void
    {
        $this->assertEquals([], $this->dispatcher->getListeners());
        $this->assertFalse($this->dispatcher->hasListeners($eventKey));
    }

    #[DataProvider('subscribedEvents')]
    public function testAddSubscriber(string $eventKey): void
    {
        $this->dispatcher->addSubscriber($this->subscriber);
        $this->assertTrue($this->dispatcher->hasListeners($eventKey));
    }

    #[DataProvider('subscribedEvents')]
    public function testRemoveSubscriber(string $eventKey): void
    {
        $this->dispatcher->addSubscriber($this->subscriber);
        $this->assertTrue($this->dispatcher->hasListeners($eventKey));
        $this->dispatcher->removeSubscriber($this->subscriber);
        $this->assertFalse($this->dispatcher->hasListeners($eventKey));
    }

    public function testCountEvent(): void
    {
        $this->assertCount(
            count($this->subscriber::getSubscribedEvents()),
            $this->subscriber::getSubscribedEvents()
        );
    }

    public static function subscribedEvents(): Generator
    {
        foreach ((static::$classSubscriber)::getSubscribedEvents() as $eventKey => $event) {
            yield [$eventKey, $event];
        }
    }
}
