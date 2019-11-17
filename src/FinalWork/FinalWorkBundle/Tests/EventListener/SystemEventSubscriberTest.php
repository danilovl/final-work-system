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

namespace FinalWork\FinalWorkBundle\Tests\EventListener;

use Doctrine\ORM\EntityManager;
use FinalWork\FinalWorkBundle\EventListener\SystemEventSubscriber;
use FinalWork\FinalWorkBundle\Services\EntityManagerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Generator;
use Symfony\Component\Security\Core\Security;

class SystemEventSubscriberTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var SystemEventSubscriber
     */
    private $listener;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher;
        $this->listener = new SystemEventSubscriber($this->mockEntityManager(), $this->mockSecurity());
    }

    /**
     * @return MockObject|EntityManager
     */
    private function mockEntityManager(): MockObject
    {
        return $this->createMock(EntityManagerService::class);
    }

    /**
     * @return MockObject|Security
     */
    private function mockSecurity(): MockObject
    {
        return $this->createMock(Security::class);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->dispatcher = null;
        $this->listener = null;
    }

    /**
     * @dataProvider subscribedEvents
     * @param string $eventKey
     */
    public function testInitialState(string $eventKey): void
    {
        $this->assertEquals([], $this->dispatcher->getListeners());
        $this->assertFalse($this->dispatcher->hasListeners($eventKey));
    }

    /**
     * @dataProvider subscribedEvents
     * @param string $eventKey
     */
    public function testAddSubscriber(string $eventKey): void
    {
        $this->dispatcher->addSubscriber($this->listener);
        $this->assertTrue($this->dispatcher->hasListeners($eventKey));
    }

    /**
     * @dataProvider subscribedEvents
     * @param string $eventKey
     */
    public function testRemoveSubscriber(string $eventKey): void
    {
        $this->dispatcher->addSubscriber($this->listener);
        $this->assertTrue($this->dispatcher->hasListeners($eventKey));
        $this->dispatcher->removeSubscriber($this->listener);
        $this->assertFalse($this->dispatcher->hasListeners($eventKey));
    }

    /**
     * @return void
     */
    public function testCountEvent(): void
    {
        $this->assertCount(
            count(SystemEventSubscriber::getSubscribedEvents()),
            $this->listener::getSubscribedEvents()
        );
    }

    /**
     * @return Generator
     */
    public function subscribedEvents(): Generator
    {
        foreach (SystemEventSubscriber::getSubscribedEvents() as $eventKey => $event) {
            yield [$eventKey, $event];
        }
    }
}
