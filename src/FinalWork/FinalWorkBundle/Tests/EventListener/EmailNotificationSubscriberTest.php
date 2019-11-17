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

use FinalWork\FinalWorkBundle\EventListener\EmailNotificationSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swift_Mailer;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Generator;
use Symfony\Component\Translation\TranslatorInterface;

class EmailNotificationSubscriberTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var EmailNotificationSubscriber
     */
    private $listener;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher;
        $this->listener = new EmailNotificationSubscriber(
            $this->mockSwiftMailer(),
            $this->mockTwigEngine(),
            $this->mockTranslator(),
            $this->getParameters()
        );
    }

    /**
     * @return MockObject|Swift_Mailer
     */
    private function mockSwiftMailer(): MockObject
    {
        return $this->createMock(Swift_Mailer::class);
    }

    /**
     * @return MockObject|TwigEngine
     */
    private function mockTwigEngine(): MockObject
    {
        return $this->createMock(TwigEngine::class);
    }

    /**
     * @return MockObject|TranslatorInterface
     */
    private function mockTranslator(): MockObject
    {
        return $this->createMock(TranslatorInterface::class);
    }

    /**
     * @return array
     */
    private function getParameters(): array
    {
        return [
            'sender' => null,
            'default_locale' => 'cs',
            'translator_domain' => null,
            'enable' => true,
        ];
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
            count(EmailNotificationSubscriber::getSubscribedEvents()),
            $this->listener::getSubscribedEvents()
        );
    }

    /**
     * @return Generator
     */
    public function subscribedEvents(): Generator
    {
        foreach (EmailNotificationSubscriber::getSubscribedEvents() as $eventKey => $event) {
            yield [$eventKey, $event];
        }
    }
}
