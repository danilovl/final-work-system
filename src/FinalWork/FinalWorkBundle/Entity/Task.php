<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace FinalWork\FinalWorkBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use FinalWork\SonataUserBundle\Entity\User;

/**
 * @ORM\Table(name="task")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\TaskRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class Task
{
    use IdTrait;
    use SimpleInformationTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var bool
     *
     * @ORM\Column(name="complete", type="boolean", options={"default":"0"})
     * @Gedmo\Versioned
     */
    private $complete = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="notify_complete", type="boolean", options={"default":"0"})
     * @Gedmo\Versioned
     */
    private $notifyComplete = false;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="tasksOwner", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var Work|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", inversedBy="tasks", fetch="EAGER")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $work;

    /**
     * @var Collection|SystemEvent[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\SystemEvent", mappedBy="task")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $systemEvents;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $deadline;

    /**
     * Task constructor.
     */
    public function __construct()
    {
        $this->systemEvents = new ArrayCollection;
    }

    /**
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return Work|null
     */
    public function getWork(): ?Work
    {
        return $this->work;
    }

    /**
     * @param Work $work
     */
    public function setWork(Work $work): void
    {
        $this->work = $work;
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->complete;
    }

    /**
     * @param bool $complete
     */
    public function setComplete(bool $complete): void
    {
        $this->complete = $complete;
    }

    /**
     * @return bool
     */
    public function isNotifyComplete(): bool
    {
        return $this->notifyComplete;
    }

    /**
     * @param bool $notifyComplete
     */
    public function setNotifyComplete(bool $notifyComplete): void
    {
        $this->notifyComplete = $notifyComplete;
    }

    /**
     * @return DateTime|null
     */
    public function getDeadline(): ?DateTime
    {
        return $this->deadline;
    }

    /**
     * @param DateTime $deadline
     */
    public function setDeadline(?DateTime $deadline): void
    {
        $this->deadline = $deadline;
    }

    /**
     * @return Collection|SystemEvent[]
     */
    public function getSystemEvents(): Collection
    {
        return $this->systemEvents;
    }

    /**
     * @param Collection $systemEvents
     */
    public function setSystemEvents(Collection $systemEvents): void
    {
        $this->systemEvents = $systemEvents;
    }

    /**
     * @return void
     */
    public function changeActive(): void
    {
        if ($this->isActive()) {
            $this->setActive(false);
        } else {
            $this->setActive(true);
        }
    }

    /**
     * @return void
     */
    public function changeComplete(): void
    {
        if ($this->isComplete()) {
            $this->setComplete(false);
            $this->setNotifyComplete(false);
        } else {
            $this->setComplete(true);
            $this->setNotifyComplete(true);
        }
    }

    /**
     * @return void
     */
    public function changeNotifyComplete(): void
    {
        if ($this->isNotifyComplete()) {
            $this->setNotifyComplete(false);
        } else {
            $this->setNotifyComplete(true);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
