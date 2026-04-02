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

namespace App\Domain\EmailNotification\Messenger;

use App\Application\Exception\RuntimeException;
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\EmailNotification\Entity\EmailNotification;
use App\Domain\EmailNotification\EventSubscriber\BaseEmailNotificationSubscriber;
use App\Domain\EmailNotification\Facade\EmailNotificationFacade;
use App\Domain\EmailNotification\Factory\EmailNotificationFactory;
use App\Domain\EmailNotification\Model\EmailNotificationModel;
use App\Domain\EmailNotification\Provider\EmailNotificationSendProvider;
use App\Domain\EmailNotification\Service\SendEmailNotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class EmailNotificationHandler
{
    public function __construct(
        private SendEmailNotificationService $sendEmailNotificationService,
        private EmailNotificationFactory $emailNotificationFactory,
        private BaseEmailNotificationSubscriber $baseEmailNotificationSubscriber,
        private EmailNotificationFacade $emailNotificationFacade,
        private EntityManagerService $entityManagerService,
        private EmailNotificationSendProvider $emailNotificationSendProvider,
        private bool $printMessage = true
    ) {}

    public function __invoke(EmailNotificationMessage $message): void
    {
        if (!$this->emailNotificationSendProvider->isEnable()) {
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

        $emailNotification = new EmailNotification;
        $emailNotification->setFrom($message->from);
        $emailNotification->setTo($message->to);
        $emailNotification->setSubject($subject);
        $emailNotification->setBody($body);

        $success = $this->sendEmailNotificationService->sendEmailNotificationBool($emailNotification);

        if ($success && $this->printMessage) {
            echo sprintf('Success send email to %s. %s', $message->to, PHP_EOL);
        }

        if (!$success && $this->printMessage) {
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
        $emailNotification = $this->emailNotificationFacade->findByUuid($message->uuid);
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
