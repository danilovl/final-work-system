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

use FinalWork\SonataUserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\FinalWorkBundle\Entity\Traits\{
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
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\SystemEventRepository")
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
     * @var SystemEventType|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\SystemEventType", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $type;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="systemEventsOwner", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var Work|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $work;

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Media", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $media;

    /**
     * @var Task|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Task", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $task;

    /**
     * @var Event|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Event", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $event;

    /**
     * @var Conversation|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Conversation", inversedBy="systemEvents", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="conversation_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $conversation;

    /**
     * @var Collection|SystemEventRecipient[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\SystemEventRecipient", mappedBy="systemEvent", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $recipient;

    /**
     * SystemEvent constructor.
     */
    public function __construct()
    {
        $this->recipient = new ArrayCollection;
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
     * @return SystemEventType|null
     */
    public function getType(): SystemEventType
    {
        return $this->type;
    }

    /**
     * @param SystemEventType $type
     */
    public function setType(SystemEventType $type): void
    {
        $this->type = $type;
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
    public function setWork(?Work $work): void
    {
        $this->work = $work;
    }

    /**
     * @return Media|null
     */
    public function getMedia(): ?Media
    {
        return $this->media;
    }

    /**
     * @param Media $media
     */
    public function setMedia(?Media $media): void
    {
        $this->media = $media;
    }

    /**
     * @return Task|null
     */
    public function getTask(): ?Task
    {
        return $this->task;
    }

    /**
     * @param Task $task
     */
    public function setTask(?Task $task): void
    {
        $this->task = $task;
    }

    /**
     * @return Event|null
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }

    /**
     * @return Conversation|null
     */
    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    /**
     * @param Conversation $conversation
     */
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

    /**
     * @param Collection $recipient
     */
    public function setRecipient(Collection $recipient): void
    {
        $this->recipient = $recipient;
    }

    /**
     * @param SystemEventRecipient $recipient
     */
    public function addRecipient(SystemEventRecipient $recipient): void
    {
        $recipient->setSystemEvent($this);
        $this->recipient[] = $recipient;
    }
}
