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

namespace App\Domain\Work\Factory;

use App\Domain\User\Entity\User;
use App\Domain\Work\DTO\Api\Input\WorkInput;
use App\Domain\Work\Model\WorkModel;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Application\Exception\UserNotExistException;
use App\Infrastructure\Exception\EntityNotFoundException;
use App\Domain\User\Bus\Query\User\{GetUserQuery, GetUserQueryResult};
use App\Domain\WorkType\Bus\Query\WorkType\{GetWorkTypeQuery, GetWorkTypeQueryResult};
use App\Domain\WorkStatus\Bus\Query\WorkStatus\{GetWorkStatusQuery, GetWorkStatusQueryResult};
use App\Domain\WorkCategory\Bus\Query\WorkCategories\{GetWorkCategoriesQuery, GetWorkCategoriesQueryResult};
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;

readonly class WorkModelFactory
{
    public function __construct(private QueryBusInterface $queryBus) {}

    public function fromWorkInput(WorkInput $input, User $supervisor): WorkModel
    {
        $workModel = new WorkModel;
        $workModel->supervisor = $supervisor;
        $workModel->title = $input->title;
        $workModel->shortcut = $input->shortcut;

        $workModel->status = $this->getWorkStatus($input->statusId);
        $workModel->type = $this->getWorkType($input->typeId);
        $workModel->author = $this->getUser($input->authorId);

        if ($input->opponentId !== null) {
            $workModel->opponent = $this->getUser($input->opponentId);
        }

        if ($input->consultantId !== null) {
            $workModel->consultant = $this->getUser($input->consultantId);
        }

        $workModel->deadline = new DateTimeImmutable($input->deadline);
        if ($input->deadlineProgram !== null) {
            $workModel->deadlineProgram = new DateTimeImmutable($input->deadlineProgram);
        }

        if ($input->categoriesIds !== null) {
            $getWorkCategoriesQuery = GetWorkCategoriesQuery::create($input->categoriesIds);
            /** @var GetWorkCategoriesQueryResult $getWorkCategoriesQueryResult */
            $getWorkCategoriesQueryResult = $this->queryBus->handle($getWorkCategoriesQuery);
            $categories = $getWorkCategoriesQueryResult->workCategories;

            $workModel->categories = new ArrayCollection($categories);
        }

        return $workModel;
    }

    private function getUser(int $id): User
    {
        $getUserQuery = GetUserQuery::create($id);
        /** @var GetUserQueryResult $getUserQueryResult */
        $getUserQueryResult = $this->queryBus->handle($getUserQuery);
        $user = $getUserQueryResult->user;

        if ($user === null) {
            throw new UserNotExistException("User with id {$id} not found.");
        }

        return $user;
    }

    private function getWorkStatus(int $id): WorkStatus
    {
        $getWorkStatusQuery = GetWorkStatusQuery::create($id);
        /** @var GetWorkStatusQueryResult $getWorkStatusQueryResult */
        $getWorkStatusQueryResult = $this->queryBus->handle($getWorkStatusQuery);
        $workStatus = $getWorkStatusQueryResult->workStatus;

        if ($workStatus === null) {
            throw new EntityNotFoundException("WorkStatus with id {$id} not found.");
        }

        return $workStatus;
    }

    private function getWorkType(int $id): WorkType
    {
        $getWorkTypeQuery = GetWorkTypeQuery::create($id);
        /** @var GetWorkTypeQueryResult $getWorkTypeQueryResult */
        $getWorkTypeQueryResult = $this->queryBus->handle($getWorkTypeQuery);
        $workType = $getWorkTypeQueryResult->workType;

        if ($workType === null) {
            throw new EntityNotFoundException("WorkType with id {$id} not found.");
        }

        return $workType;
    }
}
