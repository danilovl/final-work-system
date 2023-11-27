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

namespace App\Tests\Unit\Application\Messenger\EmailNotification;

use App\Application\EventSubscriber\EmailNotification\BaseEmailNotificationSubscriber;
use App\Application\Exception\RuntimeException;
use App\Application\Messenger\EmailNotification\{
    EmailNotificationHandler,
    EmailNotificationMessage
};
use App\Application\Service\MailerService;
use App\Domain\EmailNotificationQueue\Entity\EmailNotificationQueue;
use App\Domain\EmailNotificationQueue\Facade\EmailNotificationQueueFacade;
use App\Domain\EmailNotificationQueue\Factory\EmailNotificationQueueFactory;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use App\Application\Service\EntityManagerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;

class EmailNotificationHandlerTest extends TestCase
{
    private readonly ParameterServiceInterface $parameterService;
    private readonly MailerService $mailerService;
    private readonly EmailNotificationQueueFactory $emailNotificationQueueFactory;
    private readonly EmailNotificationQueueFacade $emailNotificationQueueFacade;
    private readonly EntityManagerService $entityManagerService;
    private readonly EmailNotificationHandler $emailNotificationHandler;
    private readonly EmailNotificationMessage $emailNotificationMessage;

    protected function setUp(): void
    {
        $this->parameterService = $this->createMock(ParameterServiceInterface::class);

        $this->mailerService = $this->createMock(MailerService::class);
        $this->mailerService->expects($this->any())
            ->method('send');

        $baseEmailNotificationSubscriber = $this->createMock(BaseEmailNotificationSubscriber::class);

        $baseEmailNotificationSubscriber->expects($this->any())
            ->method('trans')
            ->willReturn('trans');

        $baseEmailNotificationSubscriber->expects($this->any())
            ->method('renderBody')
            ->willReturn('body');

        $emailNotificationQueueModel = new EmailNotificationQueue;
        $emailNotificationQueueModel->setId(1);

        $this->emailNotificationQueueFactory = $this->createMock(EmailNotificationQueueFactory::class);
        $this->emailNotificationQueueFactory
            ->expects($this->any())
            ->method('createFromModel')
            ->willReturn($emailNotificationQueueModel);

        $this->emailNotificationQueueFacade = $this->createMock(EmailNotificationQueueFacade::class);
        $this->entityManagerService = $this->createMock(EntityManagerService::class);

        $this->emailNotificationHandler = new EmailNotificationHandler(
            $this->parameterService,
            $this->mailerService,
            $this->emailNotificationQueueFactory,
            $baseEmailNotificationSubscriber,
            $this->emailNotificationQueueFacade,
            $this->entityManagerService
        );

        $this->emailNotificationMessage = EmailNotificationMessage::createFromArray([
            'subject' => 'subject',
            'template' => 'template',
            'templateParameters' => [
                'key' => 'value',
            ],
            'locale' => 'en',
            'from' => 'test@example.com',
            'to' => 'test@example.com',
            'uuid' => 'uuid',
        ]);
    }

    public function testInvokeNotEnable(): void
    {
        $this->expectException(RuntimeException::class);

        $this->parameterService
            ->expects($this->once())
            ->method('getBoolean')
            ->willReturn(false);

        $this->expectOutputString('Email notification sending is not enable');
        $this->emailNotificationHandler->__invoke($this->emailNotificationMessage);
    }

    public function testInvokeFailedSend(): void
    {
        $this->expectException(RuntimeException::class);

        $this->parameterService
            ->expects($this->once())
            ->method('getBoolean')
            ->willReturn(true);

        $this->mailerService
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new TransportException);

        $emailNotificationQueue = new EmailNotificationQueue;
        $emailNotificationQueue->setId(1);

        $this->emailNotificationQueueFacade
            ->expects($this->once())
            ->method('getOneByUuid')
            ->willReturn($emailNotificationQueue);

        $this->expectOutputString('Failed send email to test@example.com. ' . PHP_EOL);
        $this->emailNotificationHandler->__invoke($this->emailNotificationMessage);
    }

    public function testInvokeSuccessSend(): void
    {
        $this->parameterService
            ->expects($this->once())
            ->method('getBoolean')
            ->willReturn(true);

        $this->mailerService
            ->expects($this->once())
            ->method('send');

        $this->entityManagerService
            ->expects($this->once())
            ->method('flush');

        $emailNotificationQueue = new EmailNotificationQueue;
        $emailNotificationQueue->setId(1);

        $this->emailNotificationQueueFacade
            ->expects($this->once())
            ->method('getOneByUuid')
            ->willReturn($emailNotificationQueue);

        $result = 'Success send email to test@example.com. ' . PHP_EOL;
        $result .= 'Success update email notification queue. ID: 1. ' . PHP_EOL;

        $this->expectOutputString($result);
        $this->emailNotificationHandler->__invoke($this->emailNotificationMessage);
    }

    public function testInvokeSuccessNotExistNotification(): void
    {
        $this->parameterService
            ->expects($this->once())
            ->method('getBoolean')
            ->willReturn(true);

        $this->mailerService
            ->expects($this->once())
            ->method('send');

        $this->entityManagerService
            ->expects($this->never())
            ->method('flush');

        $emailNotificationQueue = new EmailNotificationQueue;
        $emailNotificationQueue->setId(1);

        $this->emailNotificationQueueFacade
            ->expects($this->once())
            ->method('getOneByUuid')
            ->willReturn(null);

        $result = 'Success send email to test@example.com. ' . PHP_EOL;
        $result .= 'Create email notification queue. ID: 1. ' . PHP_EOL;

        $this->expectOutputString($result);
        $this->emailNotificationHandler->__invoke($this->emailNotificationMessage);
    }
}
