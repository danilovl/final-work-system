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
use App\Application\Service\EntityManagerService;
use App\Domain\EmailNotification\Entity\EmailNotification;
use App\Domain\EmailNotification\EventSubscriber\BaseEmailNotificationSubscriber;
use App\Domain\EmailNotification\Facade\EmailNotificationFacade;
use App\Domain\EmailNotification\Factory\EmailNotificationFactory;
use App\Domain\EmailNotification\Messenger\{
    EmailNotificationHandler,
    EmailNotificationMessage
};
use App\Domain\EmailNotification\Service\SendEmailNotificationService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use PHPUnit\Framework\TestCase;

class EmailNotificationHandlerTest extends TestCase
{
    private readonly ParameterServiceInterface $parameterService;
    private readonly SendEmailNotificationService $sendEmailNotificationService;
    private readonly EmailNotificationFactory $emailNotificationFactory;
    private readonly EmailNotificationFacade $emailNotificationFacade;
    private readonly EntityManagerService $entityManagerService;
    private readonly EmailNotificationHandler $emailNotificationHandler;
    private readonly EmailNotificationMessage $emailNotificationMessage;

    protected function setUp(): void
    {
        $this->parameterService = $this->createMock(ParameterServiceInterface::class);
        $this->sendEmailNotificationService = $this->createMock(SendEmailNotificationService::class);
        $baseEmailNotificationSubscriber = $this->createMock(BaseEmailNotificationSubscriber::class);

        $baseEmailNotificationSubscriber->expects($this->any())
            ->method('trans')
            ->willReturn('trans');

        $baseEmailNotificationSubscriber->expects($this->any())
            ->method('renderBody')
            ->willReturn('body');

        $emailNotification = new EmailNotification;
        $emailNotification->setId(1);

        $this->emailNotificationFactory = $this->createMock(EmailNotificationFactory::class);
        $this->emailNotificationFactory
            ->expects($this->any())
            ->method('createFromModel')
            ->willReturn($emailNotification);

        $this->emailNotificationFacade = $this->createMock(EmailNotificationFacade::class);
        $this->entityManagerService = $this->createMock(EntityManagerService::class);

        $this->emailNotificationHandler = new EmailNotificationHandler(
            $this->parameterService,
            $this->sendEmailNotificationService,
            $this->emailNotificationFactory,
            $baseEmailNotificationSubscriber,
            $this->emailNotificationFacade,
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

        $this->sendEmailNotificationService
            ->expects($this->once())
            ->method('sendEmailNotificationBool')
            ->willReturn(false);

        $emailNotification = new EmailNotification;
        $emailNotification->setId(1);

        $this->emailNotificationFacade
            ->expects($this->once())
            ->method('getOneByUuid')
            ->willReturn($emailNotification);

        $this->expectOutputString('Failed send email to test@example.com. ' . PHP_EOL);
        $this->emailNotificationHandler->__invoke($this->emailNotificationMessage);
    }

    public function testInvokeSuccessSend(): void
    {
        $this->parameterService
            ->expects($this->once())
            ->method('getBoolean')
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
            ->method('getOneByUuid')
            ->willReturn($emailNotification);

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

        $this->sendEmailNotificationService
            ->expects($this->once())
            ->method('sendEmailNotificationBool')
            ->willReturn(true);

        $this->entityManagerService
            ->expects($this->never())
            ->method('flush');

        $emailNotification = new EmailNotification;
        $emailNotification->setId(1);

        $this->emailNotificationFacade
            ->expects($this->once())
            ->method('getOneByUuid')
            ->willReturn(null);

        $result = 'Success send email to test@example.com. ' . PHP_EOL;
        $result .= 'Create email notification queue. ID: 1. ' . PHP_EOL;

        $this->expectOutputString($result);
        $this->emailNotificationHandler->__invoke($this->emailNotificationMessage);
    }
}
