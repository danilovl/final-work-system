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

namespace App\Domain\Task\Http\Api;

use App\Infrastructure\Interfaces\Bus\CommandBusInterface;
use App\Domain\Task\Bus\Command\CreateTask\CreateTaskCommand;
use App\Domain\Task\DTO\Api\Input\TaskInput;
use App\Domain\Task\DTO\Api\TaskDTO;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\Factory\TaskModelFactory;
use App\Domain\User\Service\UserService;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Response
};

readonly class TaskCreateHandle
{
    public function __construct(
        private UserService $userService,
        private TaskModelFactory $taskModelFactory,
        private CommandBusInterface $commandBus,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(TaskInput $input): JsonResponse
    {
        $user = $this->userService->getUser();

        $taskModel = $this->taskModelFactory->fromTaskInput($input, $user);

        $command = CreateTaskCommand::create($taskModel);
        /** @var Task $task */
        $task = $this->commandBus->dispatchResult($command);

        $ignoreAttributes = ['user:read:author', 'user:read:supervisor', 'user:read:opponent', 'user:read:consultant'];
        /** @var TaskDTO $taskDto */
        $taskDto = $this->objectToDtoMapper->map(
            source: $task,
            target: TaskDTO::class,
            ignoreGroups: $ignoreAttributes
        );

        return new JsonResponse(
            data: [
                'success' => true,
                'result' => $taskDto
            ],
            status: Response::HTTP_CREATED
        );
    }
}
