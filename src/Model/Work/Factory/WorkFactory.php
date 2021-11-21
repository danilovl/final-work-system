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

namespace App\Model\Work\Factory;

use App\Model\Work\Entity\Work;
use App\Model\BaseModelFactory;
use App\Model\Work\WorkModel;

class WorkFactory extends BaseModelFactory
{
    public function flushFromModel(
        WorkModel $workModel,
        Work $work = null
    ): Work {
        $work = $work ?? new Work;
        $work = $this->fromModel($work, $workModel);

        $this->entityManagerService->persistAndFlush($work);

        return $work;
    }

    public function fromModel(
        Work $work,
        WorkModel $workModel
    ): Work {
        $work->setTitle($workModel->title);
        $work->setShortcut($workModel->shortcut);
        $work->setStatus($workModel->status);
        $work->setType($workModel->type);
        $work->setAuthor($workModel->author);
        $work->setSupervisor($workModel->supervisor);
        $work->setOpponent($workModel->opponent);
        $work->setConsultant($workModel->consultant);
        $work->setDeadline($workModel->deadline);
        $work->setDeadlineProgram($workModel->deadlineProgram);
        $work->setCategories($workModel->categories);

        return $work;
    }
}
