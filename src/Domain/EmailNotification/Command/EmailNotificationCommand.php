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

namespace App\Domain\EmailNotification\Command;

use App\Domain\EmailNotification\Facade\EmailNotificationFacade;
use App\Domain\EmailNotification\Provider\EmailNotificationSendProvider;
use App\Domain\EmailNotification\Service\SendEmailNotificationService;
use App\Infrastructure\Service\EntityManagerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[AsCommand(name: 'app:email-notification-send', description: 'Send notification emails')]
class EmailNotificationCommand
{
    final public const string COMMAND_NAME = 'app:email-notification-send';

    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly EmailNotificationFacade $emailNotificationFacade,
        private readonly SendEmailNotificationService $sendEmailNotificationService,
        private readonly EmailNotificationSendProvider $emailNotificationSendProvider
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        if (!$this->emailNotificationSendProvider->isEnable()) {
            $io->error('Email notification sending is unable.');

            return Command::FAILURE;
        }

        $emailNotification = $this->emailNotificationFacade
            ->findReadyForSender();

        if ($emailNotification === null) {
            $io->error('Email notification queue is empty.');

            return Command::FAILURE;
        }

        try {
            $this->sendEmailNotificationService->sendEmailNotification($emailNotification);
            $status = true;

            $message = sprintf('Email notification with ID: %d was send.', $emailNotification->getId());
        } catch (TransportExceptionInterface $transportException) {
            $status = false;
            $message = $transportException->getMessage();
        }

        $emailNotification->setSuccess($status);
        $this->entityManagerService->flush();

        $io->{$status ? 'success' : 'error'}($message);

        return Command::SUCCESS;
    }
}
