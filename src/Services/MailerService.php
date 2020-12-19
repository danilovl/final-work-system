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

namespace App\Services;

use Symfony\Component\Mailer\{
    Envelope,
    MailerInterface
};
use Symfony\Component\Mime\RawMessage;

class MailerService implements MailerInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function send(RawMessage $message, Envelope $envelope = null): void
    {
        $this->mailer->send($message, $envelope);
    }
}
