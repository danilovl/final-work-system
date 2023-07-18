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
use App\Domain\EmailNotificationQueue\Facade\EmailNotificationQueueFacade;
use App\Domain\EmailNotificationQueue\Factory\EmailNotificationQueueFactory;
use App\Domain\EmailNotificationQueue\Model\EmailNotificationQueueModel;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\{
    Address,
    Email};

#[AsMessageHandler]
readonly class EmailNotificationHandler
{
    public function __construct(
        private ParameterServiceInterface $parameterService,
        private MailerService $mailer,
        private EmailNotificationQueueFactory $emailNotificationQueueFactory,
        private BaseEmailNotificationSubscriber $baseEmailNotificationSubscriber,
        private EmailNotificationQueueFacade $emailNotificationQueueFacade,
        private EntityManagerService $entityManagerService
    ) {}

    public function __invoke(EmailNotificationMessage $message): void
    {
        if (!$this->parameterService->getBoolean('email_notification.enable_send')) {
            echo 'Email notification sending is not enable';

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

            echo sprintf('Success send email to %s. %s', $message->to, PHP_EOL);
        } catch (TransportExceptionInterface) {
            $success = false;

            echo sprintf('Failed send email to %s. %s', $message->to, PHP_EOL);
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
        $emailNotification = $this->emailNotificationQueueFacade->getOneByUuid($message->uuid);
        if ($emailNotification !== null) {
            if (!$success) {
                return;
            }

            $emailNotification->setSuccess($success);
            $this->entityManagerService->flush();

            echo sprintf('Success update email notification queue. ID: %d. %s', $emailNotification->getId(), PHP_EOL);

            return;
        }

        $emailNotificationQueueModel = new EmailNotificationQueueModel;
        $emailNotificationQueueModel->subject = $subject;
        $emailNotificationQueueModel->to = $message->to;
        $emailNotificationQueueModel->from = $message->from;
        $emailNotificationQueueModel->body = $body;
        $emailNotificationQueueModel->uuid = $message->uuid;
        $emailNotificationQueueModel->success = $success;

        $emailNotification = $this->emailNotificationQueueFactory->createFromModel($emailNotificationQueueModel);

        echo sprintf('Success create email notification queue. ID: %d. %s', $emailNotification->getId(), PHP_EOL);
    }
}
