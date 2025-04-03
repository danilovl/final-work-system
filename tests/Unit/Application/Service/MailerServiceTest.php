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

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\MailerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\{
    Envelope,
    MailerInterface
};
use Symfony\Component\Mime\{
    Address,
    RawMessage
};

class MailerServiceTest extends TestCase
{
    private MockObject $mailerInterface;

    private MailerService $mailerService;

    protected function setUp(): void
    {
        $this->mailerInterface = $this->createMock(MailerInterface::class);
        $this->mailerService = new MailerService($this->mailerInterface);
    }

    public function testSend(): void
    {
        $message = new RawMessage('Test Message');
        $envelope = new Envelope(new Address('test@localhost'), [new Address('test@localhost')]);

        $this->mailerInterface
            ->expects($this->once())
            ->method('send')
            ->with($message, $envelope);

        $this->mailerService->send($message, $envelope);
    }
}
