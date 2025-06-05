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

namespace App\Tests\Unit\Domain\EmailNotification\EventSubscriber;

use App\Domain\EmailNotification\Entity\EmailNotification;
use App\Domain\EmailNotification\EventSubscriber\BaseEmailNotificationSubscriber;
use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use App\Domain\User\Entity\User;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BaseEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = BaseEmailNotificationSubscriber::class;

    protected EmailNotificationMessage $emailNotificationMessage;

    protected EventSubscriberInterface $subscriber;

    protected BaseEmailNotificationSubscriber $baseEmailNotificationSubscriber;

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
            $this->twigRenderService,
            $this->translator,
            $this->emailNotificationFactory,
            $this->parameterService,
            $this->bus,
            $this->emailNotificationAddToQueueProvider,
            $this->emailNotificationEnableMessengerProvider
        ) extends BaseEmailNotificationSubscriber implements EventSubscriberInterface {
            public static function getSubscribedEvents(): array
            {
                return [];
            }
        };

        $this->baseEmailNotificationSubscriber = $this->subscriber;
    }

    public function testAddEmailNotificationToQueueNotEnable(): void
    {
        $this->isEmailNotificationAddToQueueProvider = false;

        $this->userFacade
            ->expects($this->never())
            ->method('findOneByEmail');

        $emailNotificationMessage = $this->createMock(EmailNotificationMessage::class);

        $this->baseEmailNotificationSubscriber->addEmailNotificationToQueue($emailNotificationMessage);
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

        $this->baseEmailNotificationSubscriber->addEmailNotificationToQueue($this->emailNotificationMessage);
    }

    public function testAddEmailNotificationToQueueEnableMessenger(): void
    {
        $this->expectNotToPerformAssertions();

        $user = new User;
        $user->setEnabledEmailNotification(true);

        $this->baseEmailNotificationSubscriber->addEmailNotificationToQueue($this->emailNotificationMessage);
    }

    public function testAddEmailNotificationToQueueSaveLocal(): void
    {
        $this->isEmailNotificationEnableMessengerProvider = false;

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

        $this->baseEmailNotificationSubscriber->addEmailNotificationToQueue($this->emailNotificationMessage);
    }

    #[DataProvider('subscribedEvents')]
    public function testInitialState(string $eventKey): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[DataProvider('subscribedEvents')]
    public function testAddSubscriber(string $eventKey): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[DataProvider('subscribedEvents')]
    public function testRemoveSubscriber(string $eventKey): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[DataProvider('subscribedEvents')]
    public function testCountEvent(): void
    {
        $this->expectNotToPerformAssertions();
    }

    public static function subscribedEvents(): Generator
    {
        yield ['subscribedEvents'];
    }
}
