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
use App\Domain\User\Facade\UserFacade;
use App\Domain\Work\DTO\Api\Input\WorkInput;
use App\Domain\Work\Model\WorkModel;
use App\Domain\WorkCategory\Facade\WorkCategoryFacade;
use App\Domain\WorkStatus\Facade\WorkStatusFacade;
use App\Domain\WorkType\Facade\WorkTypeFacade;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

readonly class WorkModelFactory
{
    public function __construct(
        private WorkStatusFacade $workStatusFacade,
        private WorkTypeFacade $workTypeFacade,
        private UserFacade $userFacade,
        private WorkCategoryFacade $workCategoryFacade
    ) {}

    public function fromWorkInput(WorkInput $input, User $supervisor): WorkModel
    {
        $workModel = new WorkModel;
        $workModel->supervisor = $supervisor;
        $workModel->title = $input->title;
        $workModel->shortcut = $input->shortcut;

        $workModel->status = $this->workStatusFacade->getById($input->statusId);
        $workModel->type = $this->workTypeFacade->getById($input->typeId);
        $workModel->author = $this->userFacade->getById($input->authorId);

        if ($input->opponentId !== null) {
            $workModel->opponent = $this->userFacade->getById($input->opponentId);
        }

        if ($input->consultantId !== null) {
            $workModel->consultant = $this->userFacade->getById($input->consultantId);
        }

        $workModel->deadline = new DateTime($input->deadline);
        if ($input->deadlineProgram !== null) {
            $workModel->deadlineProgram = new DateTime($input->deadlineProgram);
        }

        if ($input->categoriesIds !== null) {
            $categories = $this->workCategoryFacade->getByIds($input->categoriesIds);
            $workModel->categories = new ArrayCollection($categories);
        }

        return $workModel;
    }
}
