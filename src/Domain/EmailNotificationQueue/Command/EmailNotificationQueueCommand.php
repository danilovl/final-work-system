<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\EmailNotificationQueue\Command;

use App\Application\Service\{
    MailerService,
    EntityManagerService
};
use App\Domain\EmailNotificationQueue\Facade\EmailNotificationQueueFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\{
    Address,
    Email
};

class EmailNotificationQueueCommand extends Command
{
    final public const COMMAND_NAME = 'app:email-notification-queue-send';

    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly EmailNotificationQueueFacade $emailNotificationQueueFacade,
        private readonly MailerService $mailer,
        private readonly ParameterServiceInterface $parameterService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Send notification emails');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->parameterService->getBoolean('email_notification.enable_send')) {
            $this->io->error('Email notification sending is unable.');

            return Command::FAILURE;
        }

        $emailNotification = $this->emailNotificationQueueFacade
            ->getOneReadyForSender();

        if ($emailNotification === null) {
            $this->io->error('Email notification queue is empty.');

            return Command::FAILURE;
        }

        try {
            $email = (new Email)
                ->from(new Address($emailNotification->getFrom()))
                ->to($emailNotification->getTo())
                ->subject($emailNotification->getSubject())
                ->html($emailNotification->getBody());

            $this->mailer->send($email);
            $status = true;

            $message = sprintf('Email notification with ID: %d was send.', $emailNotification->getId());
        } catch (TransportExceptionInterface $transportException) {
            $status = false;
            $message = $transportException->getMessage();
        }

        $emailNotification->setSuccess($status);
        $this->entityManagerService->flush($emailNotification);

        $this->io->{$status ? 'success' : 'error'}($message);

        return Command::SUCCESS;
    }
}
