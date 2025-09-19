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
use App\Application\Service\EntityManagerService;
use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailNotificationCommand extends Command
{
    final public const string COMMAND_NAME = 'app:email-notification-send';

    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly EmailNotificationFacade $emailNotificationFacade,
        private readonly SendEmailNotificationService $sendEmailNotificationService,
        private readonly EmailNotificationSendProvider $emailNotificationSendProvider
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Send notification emails');
    }

    #[Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->emailNotificationSendProvider->isEnable()) {
            $this->io->error('Email notification sending is unable.');

            return Command::FAILURE;
        }

        $emailNotification = $this->emailNotificationFacade
            ->getOneReadyForSender();

        if ($emailNotification === null) {
            $this->io->error('Email notification queue is empty.');

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

        $this->io->{$status ? 'success' : 'error'}($message);

        return Command::SUCCESS;
    }
}
