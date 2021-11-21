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

namespace App\Model\Task\Entity;

use App\Model\SystemEvent\Entity\SystemEvent;
use App\Model\Task\Repository\TaskRepository;
use App\Model\User\Entity\User;
use App\Model\Work\Entity\Work;
use DateTime;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @Gedmo\Loggable
 */
#[ORM\Table(name: 'task')]
#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class Task
{
    use IdTrait;
    use SimpleInformationTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'complete', type: Types::BOOLEAN, options: ['default' => '0'])]
    private bool $complete = false;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'notify_complete', type: Types::BOOLEAN, options: ['default' => '0'])]
    private bool $notifyComplete = false;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'tasksOwner')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: Work::class, fetch: 'EAGER', inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'work_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Work $work = null;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: SystemEvent::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $systemEvents;

    #[ORM\Column(name: 'deadline', type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $deadline = null;

    public function __construct()
    {
        $this->systemEvents = new ArrayCollection;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function setWork(Work $work): void
    {
        $this->work = $work;
    }

    public function isComplete(): bool
    {
        return $this->complete;
    }

    public function setComplete(bool $complete): void
    {
        $this->complete = $complete;
    }

    public function isNotifyComplete(): bool
    {
        return $this->notifyComplete;
    }

    public function setNotifyComplete(bool $notifyComplete): void
    {
        $this->notifyComplete = $notifyComplete;
    }

    public function getDeadline(): ?DateTime
    {
        return $this->deadline;
    }

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

    public function setSystemEvents(Collection $systemEvents): void
    {
        $this->systemEvents = $systemEvents;
    }

    public function changeActive(): void
    {
        $this->setActive(!$this->isActive());
    }

    public function changeComplete(): void
    {
        $isComplete = !$this->isComplete();

        $this->setComplete($isComplete);
        $this->setNotifyComplete($isComplete);
    }

    public function changeNotifyComplete(): void
    {
        $this->setNotifyComplete($this->isNotifyComplete() ? false : true);
    }

    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
