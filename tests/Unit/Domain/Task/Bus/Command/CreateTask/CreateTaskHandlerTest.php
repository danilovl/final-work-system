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

namespace App\Tests\Unit\Domain\Task\Bus\Command\CreateTask;

use App\Domain\Task\Bus\Command\CreateTask\{
    CreateTaskCommand,
    CreateTaskHandler
};
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\TaskEventDispatcher;
use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Model\TaskModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateTaskHandlerTest extends TestCase
{
    private MockObject&TaskFactory $taskFactory;

    private MockObject&TaskEventDispatcher $taskEventDispatcher;

    private CreateTaskHandler $handler;

    protected function setUp(): void
    {
        $this->taskFactory = $this->createMock(TaskFactory::class);
        $this->taskEventDispatcher = $this->createMock(TaskEventDispatcher::class);

        $this->handler = new CreateTaskHandler(
            taskFactory: $this->taskFactory,
            taskEventDispatcher: $this->taskEventDispatcher
        );
    }

    public function testInvoke(): void
    {
        $taskModel = new TaskModel;
        $command = CreateTaskCommand::create($taskModel);
        $task = new Task;

        $this->taskFactory
            ->expects($this->once())
            ->method('flushFromModel')
            ->with($taskModel)
            ->willReturn($task);

        $this->taskEventDispatcher
            ->expects($this->once())
            ->method('onTaskCreate')
            ->with($task);

        $result = $this->handler->__invoke($command);

        $this->assertSame($task, $result);
    }
}
