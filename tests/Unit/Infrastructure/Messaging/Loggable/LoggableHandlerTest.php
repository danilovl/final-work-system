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

namespace App\Tests\Unit\Infrastructure\Messaging\Loggable;

use App\Infrastructure\Messaging\Loggable\{
    LoggableHandler,
    LoggableMessage
};
use App\Infrastructure\Service\EntityManagerService;
use Gedmo\Loggable\Entity\LogEntry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoggableHandlerTest extends TestCase
{
    private LoggableHandler $loggableHandler;

    private MockObject&EntityManagerService $entityManagerService;

    protected function setUp(): void
    {
        $this->entityManagerService = $this->createMock(EntityManagerService::class);

        $this->loggableHandler = new LoggableHandler($this->entityManagerService);
    }

    public function testInvoke(): void
    {
        $loggableMessage = new LoggableMessage(new LogEntry);

        $this->entityManagerService
            ->expects($this->once())
            ->method('persistAndFlush');

        $this->expectOutputString('Success create log for class "" and username "". ' . PHP_EOL);
        $this->loggableHandler->__invoke($loggableMessage);
    }
}
