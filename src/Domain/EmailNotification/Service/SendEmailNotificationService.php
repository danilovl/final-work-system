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

namespace App\Domain\EmailNotification\Service;

use App\Infrastructure\Service\MailerService;
use App\Domain\EmailNotification\Entity\EmailNotification;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\{
    Email,
    Address
};

readonly class SendEmailNotificationService
{
    public function __construct(private MailerService $mailer) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailNotification(EmailNotification $emailNotification): void
    {
        $email = (new Email)
            ->from(new Address($emailNotification->getFrom()))
            ->to($emailNotification->getTo())
            ->subject($emailNotification->getSubject())
            ->html($emailNotification->getBody());

        $this->mailer->send($email);
    }

    public function sendEmailNotificationBool(EmailNotification $emailNotification): bool
    {
        try {
            $this->sendEmailNotification($emailNotification);
        } catch (TransportExceptionInterface) {
            return false;
        }

        return true;
    }
}
