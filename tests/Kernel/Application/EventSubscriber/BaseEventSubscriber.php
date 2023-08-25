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

namespace App\Tests\Kernel\Application\EventSubscriber;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\{
    EventDispatcher,
    EventSubscriberInterface
};

class BaseEventSubscriber extends KernelTestCase
{
    protected static string $classSubscriber;
    protected ?EventDispatcher $dispatcher;
    protected ?EventSubscriberInterface $eventSubscriber;

    protected function tearDown(): void
    {
        $this->dispatcher = null;
        $this->eventSubscriber = null;
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
        $this->dispatcher->addSubscriber($this->eventSubscriber);
        $this->assertTrue($this->dispatcher->hasListeners($eventKey));
    }

    #[DataProvider('subscribedEvents')]
    public function testRemoveSubscriber(string $eventKey): void
    {
        $this->dispatcher->addSubscriber($this->eventSubscriber);
        $this->assertTrue($this->dispatcher->hasListeners($eventKey));
        $this->dispatcher->removeSubscriber($this->eventSubscriber);
        $this->assertFalse($this->dispatcher->hasListeners($eventKey));
    }

    public function testCountEvent(): void
    {
        $this->assertCount(
            count($this->eventSubscriber::getSubscribedEvents()),
            $this->eventSubscriber::getSubscribedEvents()
        );
    }

    public static function subscribedEvents(): Generator
    {
        foreach ((static::$classSubscriber)::getSubscribedEvents() as $eventKey => $event) {
            yield [$eventKey, $event];
        }
    }
}