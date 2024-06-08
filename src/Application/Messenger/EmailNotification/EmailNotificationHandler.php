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

namespace App\Application\Messenger\EmailNotification;

use App\Application\EventSubscriber\EmailNotification\BaseEmailNotificationSubscriber;
use App\Application\Exception\RuntimeException;
use App\Application\Service\EntityManagerService;
use App\Application\Service\MailerService;
use App\Domain\EmailNotification\Facade\EmailNotificationFacade;
use App\Domain\EmailNotification\Factory\EmailNotificationFactory;
use App\Domain\EmailNotification\Model\EmailNotificationModel;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\{
    Address,
    Email
};

#[AsMessageHandler]
readonly class EmailNotificationHandler
{
    public function __construct(
        private ParameterServiceInterface $parameterService,
        private MailerService $mailer,
        private EmailNotificationFactory $emailNotificationFactory,
        private BaseEmailNotificationSubscriber $baseEmailNotificationSubscriber,
        private EmailNotificationFacade $emailNotificationFacade,
        private EntityManagerService $entityManagerService,
        private bool $printMessage = true
    ) {}

    public function __invoke(EmailNotificationMessage $message): void
    {
        if (!$this->parameterService->getBoolean('email_notification.enable_send')) {
            if ($this->printMessage) {
                echo 'Email notification sending is not enable';
            }

            throw new RuntimeException('Email notification sending is not enable');
        }

        $subject = $this->baseEmailNotificationSubscriber->trans($message->subject, $message->locale);
        $body = $this->baseEmailNotificationSubscriber->renderBody(
            $message->locale,
            $message->template,
            $message->templateParameters
        );

        $success = true;

        try {
            $email = (new Email)
                ->from(new Address($message->from))
                ->to($message->to)
                ->subject($subject)
                ->html($body);

            $this->mailer->send($email);

            if ($this->printMessage) {
                echo sprintf('Success send email to %s. %s', $message->to, PHP_EOL);
            }
        } catch (TransportExceptionInterface) {
            $success = false;

            if ($this->printMessage) {
                echo sprintf('Failed send email to %s. %s', $message->to, PHP_EOL);
            }
        }

        $this->createSuccessEmailNotificationQueue($message, $subject, $body, $success);

        if (!$success) {
            throw new RuntimeException('Failed send email');
        }
    }

    private function createSuccessEmailNotificationQueue(
        EmailNotificationMessage $message,
        string $subject,
        string $body,
        bool $success
    ): void {
        $emailNotification = $this->emailNotificationFacade->getOneByUuid($message->uuid);
        if ($emailNotification !== null) {
            if (!$success) {
                return;
            }

            $emailNotification->setSuccess($success);
            $this->entityManagerService->flush();

            if ($this->printMessage) {
                echo sprintf('Success update email notification queue. ID: %d. %s', $emailNotification->getId(), PHP_EOL);
            }

            return;
        }

        $emailNotificationModel = new EmailNotificationModel;
        $emailNotificationModel->subject = $subject;
        $emailNotificationModel->to = $message->to;
        $emailNotificationModel->from = $message->from;
        $emailNotificationModel->body = $body;
        $emailNotificationModel->uuid = $message->uuid;
        $emailNotificationModel->success = $success;

        $emailNotification = $this->emailNotificationFactory->createFromModel($emailNotificationModel);

        if ($this->printMessage) {
            echo sprintf('Create email notification queue. ID: %d. %s', $emailNotification->getId(), PHP_EOL);
        }
    }
}
