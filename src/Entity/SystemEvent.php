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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait
};
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};

/**
 * @ORM\Table(name="system_event")
 * @ORM\Entity(repositoryClass="App\Repository\SystemEventRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class SystemEvent
{
    use IdTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SystemEventType", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?SystemEventType $type = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="systemEventsOwner", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?User $owner = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Work", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Work $work = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Media $media = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Task", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Task $task = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Event $event = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Conversation", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="conversation_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Conversation $conversation = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemEventRecipient", mappedBy="systemEvent", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $recipient = null;

    public function __construct()
    {
        $this->recipient = new ArrayCollection;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    public function getType(): SystemEventType
    {
        return $this->type;
    }

    public function setType(SystemEventType $type): void
    {
        $this->type = $type;
    }

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function setWork(?Work $work): void
    {
        $this->work = $work;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): void
    {
        $this->media = $media;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): void
    {
        $this->task = $task;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): void
    {
        $this->conversation = $conversation;
    }

    /**
     * @return Collection|SystemEventRecipient[]
     */
    public function getRecipient(): Collection
    {
        return $this->recipient;
    }

    public function setRecipient(Collection $recipient): void
    {
        $this->recipient = $recipient;
    }

    public function addRecipient(SystemEventRecipient $recipient): void
    {
        $recipient->setSystemEvent($this);
        $this->recipient[] = $recipient;
    }
}
