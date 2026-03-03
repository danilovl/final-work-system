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

namespace App\Tests\Unit\Infrastructure\Messaging;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Infrastructure\Messaging\MessengerCommandBus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class MessengerCommandBusTest extends TestCase
{
    private MockObject&MessageBusInterface $messageBus;

    private MockObject&CommandInterface $command;

    private MessengerCommandBus $messengerCommandBus;

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->command = $this->createMock(CommandInterface::class);
        $this->messengerCommandBus = new MessengerCommandBus($this->messageBus);
    }

    public function testDispatch(): void
    {
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->command)
            ->willReturn(new Envelope($this->command));

        $this->messengerCommandBus->dispatch($this->command);
    }

    public function testDispatchResult(): void
    {
        $expectedResult = new stdClass;

        $handledStamp = new HandledStamp(
            $expectedResult,
            'handler_name'
        );

        $envelope = new Envelope($this->command, [$handledStamp]);

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->command, [])
            ->willReturn($envelope);

        $result = $this->messengerCommandBus->dispatchResult($this->command);

        $this->assertSame($expectedResult, $result);
    }
}
