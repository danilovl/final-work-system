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

namespace App\Model\Work;

use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use DateTime;
use App\Entity\{
    Work,
    WorkType,
    WorkStatus
};
use App\Model\Traits\TitleAwareTrait;
use App\Entity\User;

class WorkModel
{
    use TitleAwareTrait;

    public ?string $shortcut = null;
    public ?WorkStatus $status = null;
    public ?WorkType $type = null;
    public ?User $author = null;
    public ?User $supervisor = null;
    public ?User $opponent = null;
    public ?User $consultant = null;
    public ?DateTime $deadline = null;
    public ?DateTime $deadlineProgram = null;
    public ?Collection $categories = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection;
        $this->deadline = new DateTime('now');
    }

    public static function fromWork(Work $work): self
    {
        $model = new self;
        $model->title = $work->getTitle();
        $model->shortcut = $work->getShortcut();
        $model->type = $work->getType();
        $model->status = $work->getStatus();
        $model->categories = $work->getCategories();
        $model->author = $work->getAuthor();
        $model->supervisor = $work->getSupervisor();
        $model->opponent = $work->getOpponent();
        $model->consultant = $work->getConsultant();
        $model->deadline = $work->getDeadline();
        $model->deadlineProgram = $work->getDeadlineProgram();

        return $model;
    }
}
