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

namespace App\Entity;

use DateTime;
use App\Constant\TranslationConstant;
use Doctrine\Common\Collections\{
    Criteria,
    Collection,
    ArrayCollection
};
use App\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="work")
 * @ORM\Entity(repositoryClass="App\Repository\WorkRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class Work
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WorkType", inversedBy="works", fetch="EAGER")
     * @ORM\JoinColumn(name="work_type_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?WorkType $type = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WorkStatus", inversedBy="works", fetch="EAGER")
     * @ORM\JoinColumn(name="work_status_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?WorkStatus $status = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="authorWorks", fetch="EAGER")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?User $author = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="supervisorWorks", fetch="EAGER")
     * @ORM\JoinColumn(name="supervisor_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?User $supervisor = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="opponentWorks", fetch="EAGER")
     * @ORM\JoinColumn(name="opponent_id", referencedColumnName="id", nullable=true)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?User $opponent = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="consultantWorks", fetch="EAGER")
     * @ORM\JoinColumn(name="consultant_id", referencedColumnName="id", nullable=true)
     */
    private ?User $consultant = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\WorkCategory", inversedBy="works")
     * @ORM\JoinTable(name="work_to_work_category",
     *      joinColumns={@ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="work_category_id", nullable=false, referencedColumnName="id")}
     * )
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $categories = null;

    /**
     * @ORM\Column(name="title", type="string", nullable=false)
     * @Gedmo\Versioned
     */
    private ?string $title = null;

    /**
     * @ORM\Column(name="shortcut", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private ?string $shortcut = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="work")
     */
    private ?Collection $tasks = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media", mappedBy="work")
     */
    private ?Collection $medias = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemEvent", mappedBy="work")
     */
    private ?Collection $systemEvents = null;

    /**
     * @ORM\Column(name="deadline", type="date", nullable=false)
     * @Gedmo\Versioned
     */
    private ?DateTime $deadline = null;

    /**
     * @ORM\Column(name="deadline_program", type="date", nullable=true)
     * @Gedmo\Versioned
     */
    private ?DateTime $deadlineProgram = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Conversation", mappedBy="work")
     */
    private ?Collection $conversations = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EventParticipant", mappedBy="work")
     */
    private ?Collection $eventParticipants = null;

    public function __construct()
    {
        $this->deadline = new DateTime;
        $this->tasks = new ArrayCollection;
        $this->medias = new ArrayCollection;
        $this->categories = new ArrayCollection;
        $this->systemEvents = new ArrayCollection;
        $this->conversations = new ArrayCollection;
        $this->eventParticipants = new ArrayCollection;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }

    public function setSupervisor(User $supervisor): void
    {
        $this->supervisor = $supervisor;
    }

    public function getOpponent(): ?User
    {
        return $this->opponent;
    }

    public function setOpponent(?User $opponent): void
    {
        $this->opponent = $opponent;
    }

    public function getConsultant(): ?User
    {
        return $this->consultant;
    }

    public function setConsultant(?User $consultant): void
    {
        $this->consultant = $consultant;
    }

    public function getType(): ?WorkType
    {
        return $this->type;
    }

    public function setType(WorkType $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): ?WorkStatus
    {
        return $this->status;
    }

    public function setStatus(WorkStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Collection|WorkCategory[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    public function setShortcut(?string $shortcut): void
    {
        $this->shortcut = $shortcut;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function setTasks(Collection $tasks): void
    {
        $this->tasks = $tasks;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function setFile(Collection $media): void
    {
        $this->medias = $media;
    }

    public function getDeadline(): ?DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(DateTime $deadline): void
    {
        $this->deadline = $deadline;
    }

    public function getDeadlineProgram(): ?DateTime
    {
        return $this->deadlineProgram;
    }

    public function setDeadlineProgram(?DateTime $deadlineProgram): void
    {
        $this->deadlineProgram = $deadlineProgram;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function setConversations(Collection $conversations): void
    {
        $this->conversations = $conversations;
    }

    public function setEventParticipants(Collection $eventParticipants): void
    {
        $this->eventParticipants = $eventParticipants;
    }

    public function getActiveTask(): Collection
    {
        $allTask = $this->getTasks();
        $criteriaActive = Criteria::create()->where(Criteria::expr()->eq('active', true));

        return $allTask->matching($criteriaActive);
    }

    public function __toString(): string
    {
        return $this->getTitle() ?: TranslationConstant::EMPTY;
    }
}
