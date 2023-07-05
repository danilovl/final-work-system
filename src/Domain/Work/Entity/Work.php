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

namespace App\Domain\Work\Entity;

use App\Application\Constant\TranslationConstant;
use App\Domain\Conversation\Entity\Conversation;
use App\Application\Traits\Entity\{
    IdTrait,
    CreateUpdateAbleTrait
};
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\Media\Entity\Media;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\Task\Entity\Task;
use App\Domain\User\Entity\User;
use App\Domain\Work\Repository\WorkRepository;
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use DateTime;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection,
    Criteria
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'work')]
#[ORM\Entity(repositoryClass: WorkRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class Work
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    #[ORM\ManyToOne(targetEntity: WorkType::class, fetch: 'EAGER', inversedBy: 'works')]
    #[ORM\JoinColumn(name: 'work_type_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private WorkType $type;

    #[ORM\ManyToOne(targetEntity: WorkStatus::class, fetch: 'EAGER', inversedBy: 'works')]
    #[ORM\JoinColumn(name: 'work_status_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private WorkStatus $status;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'authorWorks')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private User $author;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'supervisorWorks')]
    #[ORM\JoinColumn(name: 'supervisor_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private User $supervisor;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'opponentWorks')]
    #[ORM\JoinColumn(name: 'opponent_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $opponent = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'consultantWorks')]
    #[ORM\JoinColumn(name: 'consultant_id', referencedColumnName: 'id', nullable: true)]
    private ?User $consultant = null;

    #[ORM\ManyToMany(targetEntity: WorkCategory::class, inversedBy: 'works')]
    #[ORM\JoinTable(name: 'work_to_work_category')]
    #[ORM\JoinColumn(name: 'work_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'work_category_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $categories;

    #[ORM\Column(name: 'title', type: Types::STRING, nullable: false)]
    #[Gedmo\Versioned]
    private string $title;

    #[ORM\Column(name: 'shortcut', type: Types::STRING, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $shortcut = null;

    #[ORM\OneToMany(mappedBy: 'work', targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'work', targetEntity: Media::class)]
    private Collection $medias;

    #[ORM\OneToMany(mappedBy: 'work', targetEntity: SystemEvent::class)]
    private Collection $systemEvents;

    #[ORM\Column(name: 'deadline', type: Types::DATE_MUTABLE, nullable: false)]
    #[Gedmo\Versioned]
    private DateTime $deadline;

    #[ORM\Column(name: 'deadline_program', type: Types::DATE_MUTABLE, nullable: true)]
    #[Gedmo\Versioned]
    private ?DateTime $deadlineProgram = null;

    #[ORM\OneToMany(mappedBy: 'work', targetEntity: Conversation::class)]
    private Collection $conversations;

    #[ORM\OneToMany(mappedBy: 'work', targetEntity: EventParticipant::class)]
    private Collection $eventParticipants;

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

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    public function getSupervisor(): User
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

    public function getType(): WorkType
    {
        return $this->type;
    }

    public function setType(WorkType $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): WorkStatus
    {
        return $this->status;
    }

    public function setStatus(WorkStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Collection<WorkCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }

    public function getTitle(): string
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
     * @return Collection<Task>
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
     * @return Collection<Media>
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
     * @return Collection<Conversation>
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
        return $this->title;
    }
}
