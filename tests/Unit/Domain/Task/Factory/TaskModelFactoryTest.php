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

namespace App\Tests\Unit\Domain\Task\Factory;

use App\Domain\Task\DTO\Api\Input\TaskInput;
use App\Domain\Task\Factory\TaskModelFactory;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\Work\Bus\Query\Work\{
    GetWorkQuery,
    GetWorkQueryResult
};
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskModelFactoryTest extends TestCase
{
    private MockObject&QueryBusInterface $queryBus;

    private TaskModelFactory $taskModelFactory;

    protected function setUp(): void
    {
        $this->queryBus = $this->createMock(QueryBusInterface::class);
        $this->taskModelFactory = new TaskModelFactory(queryBus: $this->queryBus);
    }

    public function testFromTaskInput(): void
    {
        $owner = new User;
        $work = new Work;

        $taskInput = new TaskInput(
            name: 'Task Name',
            description: 'Task Description',
            deadline: '2026-11-01',
            workId: 10,
            complete: true,
            active: false
        );

        $this->queryBus
            ->expects($this->once())
            ->method('handle')
            ->with($this->callback(static fn (GetWorkQuery $query): bool => $query->id === 10))
            ->willReturn(new GetWorkQueryResult($work));

        $taskModel = $this->taskModelFactory->fromTaskInput($taskInput, $owner);

        $this->assertSame('Task Name', $taskModel->name);
        $this->assertSame('Task Description', $taskModel->description);
        $this->assertNotNull($taskModel->deadline);
        $this->assertSame('2026-11-01', $taskModel->deadline->format('Y-m-d'));
        $this->assertSame($work, $taskModel->work);
        $this->assertSame($owner, $taskModel->owner);
        $this->assertTrue($taskModel->complete);
        $this->assertFalse($taskModel->active);
    }
}
