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

namespace App\Tests\Unit\Domain\EmailNotification\Messenger;

use App\Application\Exception\RuntimeException;
use App\Domain\EmailNotification\Entity\EmailNotification;
use App\Domain\EmailNotification\EventSubscriber\BaseEmailNotificationSubscriber;
use App\Domain\EmailNotification\Facade\EmailNotificationFacade;
use App\Domain\EmailNotification\Factory\EmailNotificationFactory;
use App\Domain\EmailNotification\Messenger\{
    EmailNotificationHandler,
    EmailNotificationMessage
};
use App\Domain\EmailNotification\Provider\EmailNotificationSendProvider;
use App\Domain\EmailNotification\Service\SendEmailNotificationService;
use App\Infrastructure\Service\EntityManagerService;
use PHPUnit\Framework\MockObject\{MockObject};
use PHPUnit\Framework\TestCase;

class EmailNotificationHandlerTest extends TestCase
{
    private MockObject&EmailNotificationSendProvider $emailNotificationSendProvider;

    private MockObject&SendEmailNotificationService $sendEmailNotificationService;

    private MockObject&EmailNotificationFacade $emailNotificationFacade;

    private MockObject&EntityManagerService $entityManagerService;

    private EmailNotificationHandler $emailNotificationHandler;

    private EmailNotificationMessage $emailNotificationMessage;

    protected function setUp(): void
    {
        $this->emailNotificationSendProvider = $this->createMock(EmailNotificationSendProvider::class);
        $this->sendEmailNotificationService = $this->createMock(SendEmailNotificationService::class);
        $baseEmailNotificationSubscriber = $this->createStub(BaseEmailNotificationSubscriber::class);

        $baseEmailNotificationSubscriber->method('trans')
            ->willReturn('trans');

        $baseEmailNotificationSubscriber->method('renderBody')
            ->willReturn('body');

        $emailNotification = new EmailNotification;
        $emailNotification->setId(1);

        $emailNotificationFactory = $this->createStub(EmailNotificationFactory::class);
        $emailNotificationFactory
            ->method('createFromModel')
            ->willReturn($emailNotification);

        $this->emailNotificationFacade = $this->createMock(EmailNotificationFacade::class);
        $this->entityManagerService = $this->createMock(EntityManagerService::class);

        $this->emailNotificationHandler = new EmailNotificationHandler(
            $this->sendEmailNotificationService,
            $emailNotificationFactory,
            $baseEmailNotificationSubscriber,
            $this->emailNotificationFacade,
            $this->entityManagerService,
            $this->emailNotificationSendProvider,
        );

        $this->emailNotificationMessage = new EmailNotificationMessage(
            locale: 'en',
            subject: 'subject',
            to: 'test@example.com',
            from: 'test@example.com',
            template: 'template',
            templateParameters: [
                'key' => 'value',
            ],
            uuid: 'uuid'
        );
    }

    public function testInvokeNotEnable(): void
    {
        $this->expectException(RuntimeException::class);

        $this->emailNotificationSendProvider
            ->expects($this->once())
            ->method('isEnable')
            ->willReturn(false);

        $this->sendEmailNotificationService
            ->expects($this->never())
            ->method('sendEmailNotificationBool');

        $this->emailNotificationFacade
            ->expects($this->never())
            ->method('findByUuid');

        $this->entityManagerService
            ->expects($this->never())
            ->method('flush');

        $this->expectOutputString('Email notification sending is not enable');
        $this->emailNotificationHandler->__invoke($this->emailNotificationMessage);
    }

    public function testInvokeFailedSend(): void
    {
        $this->expectException(RuntimeException::class);

        $this->emailNotificationSendProvider
            ->expects($this->once())
            ->method('isEnable')
            ->willReturn(true);

        $this->sendEmailNotificationService
            ->expects($this->once())
            ->method('sendEmailNotificationBool')
            ->willReturn(false);

        $emailNotification = new EmailNotification;
        $emailNotification->setId(1);

        $this->emailNotificationFacade
            ->expects($this->once())
            ->method('findByUuid')
            ->willReturn($emailNotification);

        $this->entityManagerService
            ->expects($this->never())
            ->method('flush');

        $this->expectOutputString('Failed send email to test@example.com. ' . PHP_EOL);
        $this->emailNotificationHandler->__invoke($this->emailNotificationMessage);
    }

    public function testInvokeSuccessSend(): void
    {
        $this->emailNotificationSendProvider
            ->expects($this->once())
            ->method('isEnable')
            ->willReturn(true);

        $this->sendEmailNotificationService
            ->expects($this->once())
            ->method('sendEmailNotificationBool')
            ->willReturn(true);

        $this->entityManagerService
            ->expects($this->once())
            ->method('flush');

        $emailNotification = new EmailNotification;
        $emailNotification->setId(1);

        $this->emailNotificationFacade
            ->expects($this->once())
            ->method('findByUuid')
            ->willReturn($emailNotification);

        $result = 'Success send email to test@example.com. ' . PHP_EOL;
        $result .= 'Success update email notification queue. ID: 1. ' . PHP_EOL;

        $this->expectOutputString($result);
        $this->emailNotificationHandler->__invoke($this->emailNotificationMessage);
    }

    public function testInvokeSuccessNotExistNotification(): void
    {
        $this->emailNotificationSendProvider
            ->expects($this->once())
            ->method('isEnable')
            ->willReturn(true);

        $this->sendEmailNotificationService
            ->expects($this->once())
            ->method('sendEmailNotificationBool')
            ->willReturn(true);

        $this->entityManagerService
            ->expects($this->never())
            ->method('flush');

        $this->emailNotificationFacade
            ->expects($this->once())
            ->method('findByUuid')
            ->willReturn(null);

        $result = 'Success send email to test@example.com. ' . PHP_EOL;
        $result .= 'Create email notification queue. ID: 1. ' . PHP_EOL;

        $this->expectOutputString($result);
        $this->emailNotificationHandler->__invoke($this->emailNotificationMessage);
    }
}
