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

namespace App\Tests\Unit\Domain\Task\Http\Api;

use App\Infrastructure\Interfaces\Bus\CommandBusInterface;
use App\Domain\Task\Bus\Command\CreateTask\CreateTaskCommand;
use App\Domain\Task\DTO\Api\Input\TaskInput;
use App\Domain\Task\DTO\Api\TaskDTO;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\Factory\TaskModelFactory;
use App\Domain\Task\Http\Api\TaskCreateHandle;
use App\Domain\Task\Model\TaskModel;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskCreateHandleTest extends TestCase
{
    private MockObject&UserService $userService;

    private MockObject&TaskModelFactory $taskModelFactory;

    private MockObject&CommandBusInterface $commandBus;

    private MockObject&ObjectToDtoMapperInterface $objectToDtoMapper;

    private TaskCreateHandle $handle;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->taskModelFactory = $this->createMock(TaskModelFactory::class);
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->objectToDtoMapper = $this->createMock(ObjectToDtoMapperInterface::class);

        $this->handle = new TaskCreateHandle(
            userService: $this->userService,
            taskModelFactory: $this->taskModelFactory,
            commandBus: $this->commandBus,
            objectToDtoMapper: $this->objectToDtoMapper
        );
    }

    public function testInvoke(): void
    {
        $user = new User;
        $taskInput = new TaskInput(
            name: 'Task Name',
            description: 'Task Description',
            deadline: '2026-11-01',
            workId: 1
        );
        $taskModel = new TaskModel;
        $task = new Task;
        $taskDto = new TaskDTO(
            id: 1,
            active: true,
            name: 'Task Name',
            description: 'Task Description',
            complete: false,
            notifyComplete: false,
            deadline: '2026-11-01',
            work: null
        );

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->taskModelFactory
            ->expects($this->once())
            ->method('fromTaskInput')
            ->with($taskInput, $user)
            ->willReturn($taskModel);

        $this->commandBus
            ->expects($this->once())
            ->method('dispatchResult')
            ->with($this->isInstanceOf(CreateTaskCommand::class))
            ->willReturn($task);

        $this->objectToDtoMapper
            ->expects($this->once())
            ->method('map')
            ->with(
                $task,
                TaskDTO::class,
                [],
                ['user:read:author', 'user:read:supervisor', 'user:read:opponent', 'user:read:consultant']
            )
            ->willReturn($taskDto);

        $response = $this->handle->__invoke($taskInput);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertIsString($content);
        /** @var array{success: bool, result: array{id: int, name: string}} $data */
        $data = json_decode($content, true);
        $this->assertTrue($data['success']);
        $this->assertSame(1, $data['result']['id']);
        $this->assertSame('Task Name', $data['result']['name']);
    }
}
