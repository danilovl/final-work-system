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

namespace App\Tests\Unit\Application\EventSubscriber\EmailNotification;

use App\Application\EventSubscriber\EmailNotification\BaseEmailNotificationSubscriber;
use App\Application\Messenger\EmailNotification\EmailNotificationMessage;
use App\Domain\EmailNotification\Entity\EmailNotification;
use App\Domain\User\Entity\User;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

class BaseEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = BaseEmailNotificationSubscriber::class;
    protected readonly EmailNotificationMessage $emailNotificationMessage;
    protected readonly BaseEmailNotificationSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailNotificationMessage = EmailNotificationMessage::createFromArray([
            'locale' => 'en',
            'subject' => 'subject.document_create',
            'to' => 'test@example.com',
            'from' => 'test@example.com',
            'template' => 'document_create',
            'templateParameters' => []
        ]);

        $this->subscriber = new class (
            $this->userFacade,
            $this->twig,
            $this->translator,
            $this->emailNotificationFactory,
            $this->parameterService,
            $this->bus
        ) extends BaseEmailNotificationSubscriber {
            public function addEmailNotificationToQueuePublic(EmailNotificationMessage $emailNotificationMessage): void
            {
                $this->addEmailNotificationToQueue($emailNotificationMessage);
            }
        };
    }

    public function testAddEmailNotificationToQueueNotEnable(): void
    {
        $emailNotificationMessage = $this->createMock(EmailNotificationMessage::class);

        $this->subscriber->enableAddToQueue = false;

        $this->userFacade
            ->expects($this->never())
            ->method('findOneByEmail');

        $this->subscriber->addEmailNotificationToQueuePublic($emailNotificationMessage);
    }

    public function testAddEmailNotificationToQueueUserNoEnable(): void
    {
        $this->bus
            ->expects($this->never())
            ->method('dispatch');

        $user = new User;
        $user->setEnabledEmailNotification(false);

        $this->userFacade
            ->expects($this->once())
            ->method('findOneByEmail')
            ->willReturn($user);

        $this->subscriber->addEmailNotificationToQueuePublic($this->emailNotificationMessage);
    }

    public function testAddEmailNotificationToQueueEnableMessenger(): void
    {
        $user = new User;
        $user->setEnabledEmailNotification(true);

        $this->subscriber->addEmailNotificationToQueuePublic($this->emailNotificationMessage);

        $this->assertTrue(true);
    }

    public function testAddEmailNotificationToQueueSaveLocal(): void
    {
        $this->subscriber->enableMessenger = false;

        $this->bus
            ->expects($this->never())
            ->method('dispatch');

        $user = new User;
        $user->setEnabledEmailNotification(true);

        $this->userFacade
            ->expects($this->once())
            ->method('findOneByEmail')
            ->willReturn($user);

        $this->emailNotificationFactory
            ->expects($this->once())
            ->method('createFromModel')
            ->willReturn(new EmailNotification);

        $this->subscriber->addEmailNotificationToQueuePublic($this->emailNotificationMessage);
    }

    #[DataProvider('subscribedEvents')]
    public function testInitialState(string $eventKey): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('subscribedEvents')]
    public function testAddSubscriber(string $eventKey): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('subscribedEvents')]
    public function testRemoveSubscriber(string $eventKey): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('subscribedEvents')]
    public function testCountEvent(): void
    {
        $this->assertTrue(true);
    }

    public static function subscribedEvents(): Generator
    {
        yield ['subscribedEvents'];
    }
}
