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

use App\Application\Interfaces\Bus\QueryInterface;
use App\Infrastructure\Messaging\MessengerQueryBus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\{
    Envelope,
    MessageBusInterface
};
use Symfony\Component\Messenger\Stamp\HandledStamp;

class MessengerQueryBusTest extends TestCase
{
    private MockObject&MessageBusInterface $queryBus;

    private QueryInterface $query;

    private MessengerQueryBus $messengerQueryBus;

    protected function setUp(): void
    {
        $this->queryBus = $this->createMock(MessageBusInterface::class);
        $this->query = $this->createStub(QueryInterface::class);
        $this->messengerQueryBus = new MessengerQueryBus($this->queryBus);
    }

    public function testHandle(): void
    {
        $expectedResult = new stdClass;

        $handledStamp = new HandledStamp(
            $expectedResult,
            'handler_name'
        );

        $envelope = new Envelope($this->query, [$handledStamp]);

        $this->queryBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->query)
            ->willReturn($envelope);

        $result = $this->messengerQueryBus->handle($this->query);

        $this->assertSame($expectedResult, $result);
    }
}
