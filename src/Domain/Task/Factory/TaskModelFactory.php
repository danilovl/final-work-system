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

namespace App\Domain\Task\Factory;

use App\Domain\Task\DTO\Api\Input\TaskInput;
use App\Domain\Task\Model\TaskModel;
use App\Domain\User\Entity\User;
use DateTimeImmutable;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\Work\Bus\Query\Work\{GetWorkQuery, GetWorkQueryResult};
use App\Infrastructure\Exception\EntityNotFoundException;
use App\Domain\Work\Entity\Work;

readonly class TaskModelFactory
{
    public function __construct(private QueryBusInterface $queryBus) {}

    public function fromTaskInput(TaskInput $input, User $owner): TaskModel
    {
        $taskModel = new TaskModel;
        $taskModel->name = $input->name;
        $taskModel->description = $input->description;
        $taskModel->complete = $input->complete;
        $taskModel->active = $input->active;
        $taskModel->deadline = new DateTimeImmutable($input->deadline);
        $taskModel->work = $this->getWork($input->workId);
        $taskModel->owner = $owner;

        return $taskModel;
    }

    private function getWork(int $id): Work
    {
        $getWorkQuery = GetWorkQuery::create($id);
        /** @var GetWorkQueryResult $getWorkQueryResult */
        $getWorkQueryResult = $this->queryBus->handle($getWorkQuery);
        $work = $getWorkQueryResult->work;

        if ($work === null) {
            throw new EntityNotFoundException("Work with id {$id} not found.");
        }

        return $work;
    }
}
