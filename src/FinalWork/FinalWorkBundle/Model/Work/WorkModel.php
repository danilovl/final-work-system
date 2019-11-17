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

namespace FinalWork\FinalWorkBundle\Model\Work;

use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use DateTime;
use FinalWork\FinalWorkBundle\Entity\Work;
use FinalWork\FinalWorkBundle\Model\Traits\TitleAwareTrait;
use Symfony\Component\Validator\Constraints as Assert;

class WorkModel
{
    use TitleAwareTrait;

    /**
     * @var string|null
     */
    public $shortcut;

    /**
     * @Assert\NotBlank()
     */
    public $status;

    /**
     * @Assert\NotBlank()
     */
    public $type;

    /**
     * @Assert\NotBlank()
     */
    public $author;

    /**
     * @Assert\NotBlank()
     */
    public $supervisor;

    /**
     * @var null
     */
    public $opponent;

    /**
     * @var null
     */
    public $consultant;

    /**
     * @var DateTime|null
     * @Assert\NotBlank()
     */
    public $deadline;

    /**
     * @var DateTime|null
     */
    public $deadlineProgram;

    /**
     * @var Collection
     */
    public $categories;

    /**
     * WorkModel constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->deadline = new DateTime('now');
    }

    /**
     * @param Work $work
     * @return WorkModel
     */
    public static function fromWork(Work $work): self
    {
        $model = new self();
        $model->title = $work->getTitle();
        $model->shortcut = $work->getShortcut();
        $model->type = $work->getType();
        $model->status = $work->getStatus();
        $model->categories = $work->getCategories();
        $model->author = $work->getAuthor();
        $model->opponent = $work->getOpponent();
        $model->consultant = $work->getConsultant();
        $model->deadline = $work->getDeadline();
        $model->deadlineProgram = $work->getDeadlineProgram();

        return $model;
    }
}
