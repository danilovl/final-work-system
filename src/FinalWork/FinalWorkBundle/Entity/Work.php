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
use Exception;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Helper\FunctionHelper;
use FinalWork\SonataUserBundle\Entity\User;
use Doctrine\Common\Collections\{
    Criteria,
    Collection,
    ArrayCollection
};
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="work")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\WorkRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class Work
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var WorkType|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\WorkType", inversedBy="works", fetch="EAGER")
     * @ORM\JoinColumn(name="work_type_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $type;

    /**
     * @var WorkStatus|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\WorkStatus", inversedBy="works", fetch="EAGER")
     * @ORM\JoinColumn(name="work_status_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $status;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="authorWorks", fetch="EAGER")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $author;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="supervisorWorks", fetch="EAGER")
     * @ORM\JoinColumn(name="supervisor_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $supervisor;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="opponentWorks", fetch="EAGER")
     * @ORM\JoinColumn(name="opponent_id", referencedColumnName="id", nullable=true)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $opponent;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="consultantWorks", fetch="EAGER")
     * @ORM\JoinColumn(name="consultant_id", referencedColumnName="id", nullable=true)
     */
    private $consultant;

    /**
     * @var Collection|WorkCategory[]
     *
     * @ORM\ManyToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\WorkCategory", inversedBy="works")
     * @ORM\JoinTable(name="work_to_work_category",
     *      joinColumns={@ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="work_category_id", nullable=false, referencedColumnName="id")}
     * )
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $categories;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", nullable=false)
     * @Gedmo\Versioned
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="shortcut", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private $shortcut;

    /**
     * @var Collection|Task[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Task", mappedBy="work")
     */
    private $tasks;

    /**
     * @var Collection|Media[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Media", mappedBy="work")
     */
    private $medias;

    /**
     * @var Collection|SystemEvent[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\SystemEvent", mappedBy="work")
     */
    private $systemEvents;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="deadline", type="date", nullable=false)
     * @Gedmo\Versioned
     */
    private $deadline;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="deadline_program", type="date", nullable=true)
     * @Gedmo\Versioned
     */
    private $deadlineProgram;

    /**
     * @var Collection|Conversation[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Conversation", mappedBy="work")
     */
    private $conversations;

    /**
     * @var Collection|EventParticipant[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\EventParticipant", mappedBy="work")
     */
    private $eventParticipants;

    /**
     * Work constructor.
     */
    public function __construct()
    {
        $this->deadline = new DateTime('now');
        $this->tasks = new ArrayCollection;
        $this->medias = new ArrayCollection;
        $this->categories = new ArrayCollection;
        $this->systemEvents = new ArrayCollection;
        $this->conversations = new ArrayCollection;
        $this->eventParticipants = new ArrayCollection;
    }

    /**
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * @param User $author
     */
    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    /**
     * @return User|null
     */
    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }

    /**
     * @param User $supervisor
     */
    public function setSupervisor(User $supervisor): void
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @return User|null
     */
    public function getOpponent(): ?User
    {
        return $this->opponent;
    }

    /**
     * @param User $opponent
     */
    public function setOpponent(?User $opponent): void
    {
        $this->opponent = $opponent;
    }

    /**
     * @return User|null
     */
    public function getConsultant(): ?User
    {
        return $this->consultant;
    }

    /**
     * @param User $consultant
     */
    public function setConsultant(?User $consultant): void
    {
        $this->consultant = $consultant;
    }

    /**
     * @return WorkType|null
     */
    public function getType(): ?WorkType
    {
        return $this->type;
    }

    /**
     * @param WorkType $type
     */
    public function setType(WorkType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return WorkStatus|null
     */
    public function getStatus(): ?WorkStatus
    {
        return $this->status;
    }

    /**
     * @param WorkStatus $status
     */
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

    /**
     * @param Collection $categories
     */
    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    /**
     * @param string $shortcut
     */
    public function setShortcut(string $shortcut): void
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

    /**
     * @param Collection $tasks
     */
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

    /**
     * @param Collection $media
     */
    public function setFile(Collection $media): void
    {
        $this->medias = $media;
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
    public function setDeadline(DateTime $deadline): void
    {
        $this->deadline = $deadline;
    }

    /**
     * @return DateTime|null
     */
    public function getDeadlineProgram(): ?DateTime
    {
        return $this->deadlineProgram;
    }

    /**
     * @param DateTime $deadlineProgram
     */
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

    /**
     * @param Collection $conversations
     */
    public function setConversations(Collection $conversations): void
    {
        $this->conversations = $conversations;
    }

    /**
     * @return Collection|EventParticipant[]
     */
    public function getEventParticipants(): Collection
    {
        return $this->eventParticipants;
    }

    /**
     * @return Collection
     */
    public function getEvent(): Collection
    {
        $events = new ArrayCollection;
        foreach ($this->getEventParticipants() as $eventParticipant) {
            $events->add($eventParticipant->getEvent());
        }

        return $events;
    }

    /**
     * @param Collection $eventParticipants
     */
    public function setEventParticipants(Collection $eventParticipants): void
    {
        $this->eventParticipants = $eventParticipants;
    }

    /**
     * @return Collection
     */
    public function getActiveTask(): Collection
    {
        $allTask = $this->getTasks();
        $criteriaActive = Criteria::create()->where(Criteria::expr()->eq('active', true));

        return $allTask->matching($criteriaActive);
    }

    /**
     * @param Collection $tasks
     * @return float
     */
    public function getCompleteTaskPercentage(Collection $tasks = null): float
    {
        if ($tasks === null) {
            $tasks = $this->getActiveTask();
        }

        $taskCount = $tasks->count();
        $completeTasks = 0;

        foreach ($tasks as $task) {
            if ($task->isComplete()) {
                $completeTasks++;
            }
        }

        return round(($completeTasks / $taskCount) * 100, 0);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isAuthorSupervisorOpponent(User $user): bool
    {
        return $this->isAuthor($user) || $this->isSupervisor($user) || $this->isOpponent($user);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isAuthorSupervisor(User $user): bool
    {
        return ($this->isAuthor($user) || $this->isSupervisor($user));
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isAuthor(User $user): bool
    {
        return $this->getAuthor()->getId() === $user->getId();
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isSupervisor(User $user): bool
    {
        if ($this->getSupervisor()) {
            return $this->getSupervisor()->getId() === $user->getId();
        }

        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isOpponent(User $user): bool
    {
        if ($this->getOpponent()) {
            return $this->getOpponent()->getId() === $user->getId();
        }

        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isConsultant(User $user): bool
    {
        if ($this->getConsultant()) {
            return $this->getConsultant()->getId() === $user->getId();
        }

        return false;
    }

    /**
     * @param bool|null $author
     * @param bool|null $supervisor
     * @param bool|null $consultant
     * @param bool|null $opponent
     * @return array
     */
    public function getUsers(
        bool $author = null,
        bool $supervisor = null,
        bool $consultant = null,
        bool $opponent = null
    ): array {
        $users = [];

        if ($author === true && $this->getAuthor() !== null) {
            $users[] = $this->getAuthor();
        }

        if ($opponent === true && $this->getOpponent() !== null) {
            $users[] = $this->getOpponent();
        }

        if ($supervisor === true && $this->getSupervisor() !== null) {
            $users[] = $this->getSupervisor();
        }

        if ($consultant === true && $this->getConsultant() !== null) {
            $users[] = $this->getConsultant();
        }

        return $users;
    }

    /**
     * @return array
     */
    public function getAllUsers(): array
    {
        return $this->getUsers(true, true, true, true);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isParticipant(User $user): bool
    {
        $participants = $this->getAllUsers();

        foreach ($participants as $participant) {
            if ($participant->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User $userOne
     * @param User $userTwo
     * @return bool|Conversation
     */
    public function checkConversation(
        User $userOne,
        User $userTwo
    ): ?Conversation {
        $workConversations = $this->getConversations();

        $conversationUserArray = [$userOne->getId(), $userTwo->getId()];
        if ($workConversations) {
            /** @var Conversation $workConversation */
            foreach ($workConversations as $workConversation) {
                $isCompare = FunctionHelper::compareSimpleTwoArray($workConversation->getParticipantIds(), $conversationUserArray);
                if ($isCompare) {
                    return $workConversation;
                }
            }
        }

        return null;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getDeadlineDays(): int
    {
        $now = new DateTime;
        $d = $now->diff($this->getDeadline())->d;

        return $now->diff($this->getDeadline())->invert ? -$d : $d;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getDeadlineProgramDays(): int
    {
        $now = new DateTime;
        $d = $now->diff($this->getDeadlineProgram())->d;

        return $now->diff($this->getDeadline())->invert ? -$d : $d;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getTitle() ?: TranslationConstant::EMPTY;
    }
}
