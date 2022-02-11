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

namespace App\Application\RabbitMq\Consumer;

use App\Application\DataTransferObject\EventSubscriber\EmailNotificationToQueueData;
use App\Application\EventSubscriber\EmailNotification\BaseEmailNotificationSubscriber;
use App\Application\Service\MailerService;
use App\Domain\EmailNotificationQueue\EmailNotificationQueueModel;
use App\Domain\EmailNotificationQueue\Entity\EmailNotificationQueue;
use App\Domain\EmailNotificationQueue\Factory\EmailNotificationQueueFactory;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateTime;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\{
    Address,
    Email
};

class EmailNotificationConsumer implements ConsumerInterface
{
    public function __construct(
        private ParameterServiceInterface $parameterService,
        private MailerService $mailer,
        private EmailNotificationQueueFactory $emailNotificationQueueFactory,
        private BaseEmailNotificationSubscriber $baseEmailNotificationSubscriber,
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        if (!$this->parameterService->getBoolean('email_notification.enable_send')) {
            echo 'Email notification sending is not enable';

            return ConsumerInterface::MSG_REJECT;
        }

        $queueData = EmailNotificationToQueueData::createFromJson($msg->body);

        $body = $this->baseEmailNotificationSubscriber->renderBody(
            $queueData->locale,
            $queueData->template,
            $queueData->templateParameters
        );

        $success = true;

        try {
            $email = (new Email)
                ->from(new Address($queueData->from))
                ->to($queueData->to)
                ->subject($queueData->subject)
                ->html($body);

            $this->mailer->send($email);

            echo sprintf('Success send email to %s. %s', $queueData->to, PHP_EOL);
        } catch (TransportExceptionInterface) {
            $success = false;

            echo sprintf('Failed send email to %s. %s', $queueData->to, PHP_EOL);
        }

        $emailNotificationQueue = $this->createSuccessEmailNotificationQueue($queueData, $body, $success);

        echo sprintf('Success create email notification queue. ID: %d. %s', $emailNotificationQueue->getId(), PHP_EOL);

        return ConsumerInterface::MSG_ACK;
    }

    private function createSuccessEmailNotificationQueue(
        EmailNotificationToQueueData $queueData,
        string $body,
        bool $success
    ): EmailNotificationQueue {
        $emailNotificationQueueModel = new EmailNotificationQueueModel;
        $emailNotificationQueueModel->subject = $queueData->subject;
        $emailNotificationQueueModel->to = $queueData->to;
        $emailNotificationQueueModel->from = $queueData->from;
        $emailNotificationQueueModel->body = $body;
        $emailNotificationQueueModel->success = $success;
        $emailNotificationQueueModel->sendedAt = new DateTime;

        return $this->emailNotificationQueueFactory->createFromModel($emailNotificationQueueModel);
    }
}
