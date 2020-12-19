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

namespace App\Command;

use App\Model\EmailNotificationQueue\EmailNotificationQueueFacade;
use Danilovl\ParameterBundle\Services\ParameterService;
use DateTime;
use App\Services\{
    MailerService,
    EntityManagerService
};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\{
    Email,
    Address
};

class EmailNotificationQueueCommand extends Command
{
    protected static $defaultName = 'app:email-notification-queue-send';

    private SymfonyStyle $io;

    public function __construct(
        private EntityManagerService $entityManagerService,
        private EmailNotificationQueueFacade $emailNotificationQueueFacade,
        private MailerService $mailer,
        private ParameterService $parameterService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Send notification emails');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->parameterService->get('email_notification.enable_send')) {
            $this->io->error('Email notification sending is unable');

            return Command::FAILURE;
        }

        $emailNotificationQueue = $this->emailNotificationQueueFacade
            ->getOneReadyForSender();

        if ($emailNotificationQueue === null) {
            $this->io->error('Email notification queue is empty');

            return Command::FAILURE;
        }

        try {
            $email = (new Email)
                ->from(new Address($emailNotificationQueue->getFrom()))
                ->to($emailNotificationQueue->getTo())
                ->subject($emailNotificationQueue->getSubject())
                ->html($emailNotificationQueue->getBody());

            $this->mailer->send($email);
            $status = true;

            $message = sprintf("Email notification with ID: %d was send",
                $emailNotificationQueue->getId()
            );
        } catch (TransportException $e) {
            $status = false;
            $message = $e->getMessage();
        }

        $emailNotificationQueue->setSuccess($status);
        $emailNotificationQueue->setSendedAt(new DateTime);

        $this->entityManagerService->flush($emailNotificationQueue);

        $this->io->{$status ? 'success' : 'error'}($message);

        return Command::SUCCESS;
    }
}
