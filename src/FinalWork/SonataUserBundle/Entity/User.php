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

namespace FinalWork\SonataUserBundle\Entity;

use Doctrine\Common\Collections\{
    Criteria,
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use FinalWork\FinalWorkBundle\Constant\{
    UserRoleConstant,
    WorkStatusConstant,
    WorkUserTypeConstant
};
use FinalWork\FinalWorkBundle\Entity\{Comment,
    Work,
    Task,
    Media,
    Event,
    MediaType,
    SystemEvent,
    WorkCategory,
    Conversation,
    EventAddress,
    EventSchedule,
    MediaCategory,
    EventParticipant,
    ConversationMessage,
    SystemEventRecipient,
    ConversationParticipant,
    ConversationMessageStatus
};
use Generator;
use Sonata\UserBundle\Entity\BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="user")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 * @ORM\Entity(repositoryClass="FinalWork\SonataUserBundle\Entity\Repository\UserRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 */
class User extends BaseUser
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Collection|Group[]
     *
     * @ORM\ManyToMany(targetEntity="FinalWork\SonataUserBundle\Entity\Group", inversedBy="users")
     * @ORM\JoinTable(name="user_to_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * @var Media|null
     *
     * @ORM\OneToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Media", fetch="EAGER")
     * @ORM\JoinColumn(name="profile_image_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $profileImage;

    /**
     * @var string|null
     *
     * @ORM\Column(name="skype", type="string", nullable=true)
     */
    protected $skype;

    /**
     * @var string|null
     *
     * @ORM\Column(name="degree_before", type="string", nullable=true)
     */
    protected $degreeBefore;

    /**
     * @var string|null
     *
     * @ORM\Column(name="degree_after", type="string", nullable=true)
     */
    protected $degreeAfter;

    /**
     * @var string|null
     *
     * @ORM\Column(name="message_greeting", type="string", nullable=true)
     */
    protected $messageGreeting;

    /**
     * @var string|null
     *
     * @ORM\Column(name="message_signature", type="string", nullable=true)
     */
    protected $messageSignature;

    /**
     * @var Collection|Media[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Media", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $mediaOwner;

    /**
     * @var Collection|MediaCategory[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\MediaCategory", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $mediaCategoriesOwner;

    /**
     * @var Collection|Work[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", mappedBy="author", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $authorWorks;

    /**
     * @var Collection|Work[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", mappedBy="supervisor", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $supervisorWorks;

    /**
     * @var Collection|Work[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", mappedBy="opponent", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $opponentWorks;

    /**
     * @var Collection|Work[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", mappedBy="consultant", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $consultantWorks;

    /**
     * @var Collection|Task[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Task", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $tasksOwner;

    /**
     * @var Collection|WorkCategory[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\WorkCategory", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $workCategoriesOwner;

    /**
     * @var Collection|EventAddress[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\EventAddress", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $eventAddressOwner;

    /**
     * @var Collection|Event[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Event", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $eventsOwner;

    /**
     * @var Collection|EventParticipant[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\EventParticipant", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $eventsParticipant;

    /**
     * @var Collection|Conversation[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Conversation", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $conversationsOwner;

    /**
     * @var Collection|ConversationParticipant[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationParticipant", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $conversationsParticipant;

    /**
     * @var Collection|ConversationMessage[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationMessage", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $conversationMessages;

    /**
     * @var Collection|ConversationMessageStatus[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\ConversationMessageStatus", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $conversationMessageStatus;

    /**
     * @var Collection|Comment[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Comment", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $comments;

    /**
     * @var Collection|EventSchedule[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\EventSchedule", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $eventsSchedule;

    /**
     * @var Collection|SystemEvent[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\SystemEvent", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $systemEventsOwner;

    /**
     * @var Collection|SystemEventRecipient[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\SystemEventRecipient", mappedBy="recipient", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    protected $systemEventsRecipient;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->tasksOwner = new ArrayCollection;
        $this->mediaOwner = new ArrayCollection;
        $this->mediaCategoriesOwner = new ArrayCollection;
        $this->comments = new ArrayCollection;
        $this->groups = new ArrayCollection;
        $this->authorWorks = new ArrayCollection;
        $this->supervisorWorks = new ArrayCollection;
        $this->opponentWorks = new ArrayCollection;
        $this->consultantWorks = new ArrayCollection;
        $this->workCategoriesOwner = new ArrayCollection;
        $this->eventsOwner = new ArrayCollection;
        $this->eventsSchedule = new ArrayCollection;
        $this->eventsParticipant = new ArrayCollection;
        $this->eventAddressOwner = new ArrayCollection;
        $this->conversationsOwner = new ArrayCollection;
        $this->conversationsParticipant = new ArrayCollection;
        $this->conversationMessages = new ArrayCollection;
        $this->conversationMessageStatus = new ArrayCollection;
        $this->systemEventsOwner = new ArrayCollection;
        $this->systemEventsRecipient = new ArrayCollection;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getDegreeBefore(): ?string
    {
        return $this->degreeBefore;
    }

    /**
     * @param string|null $degreeBefore
     */
    public function setDegreeBefore(?string $degreeBefore): void
    {
        $this->degreeBefore = $degreeBefore;
    }

    /**
     * @return string|null
     */
    public function getDegreeAfter(): ?string
    {
        return $this->degreeAfter;
    }

    /**
     * @param string|null $degreeAfter
     */
    public function setDegreeAfter(?string $degreeAfter): void
    {
        $this->degreeAfter = $degreeAfter;
    }

    /**
     * @return string|null
     */
    public function getMessageGreeting(): ?string
    {
        return $this->messageGreeting;
    }

    /**
     * @param string|null $messageGreeting
     */
    public function setMessageGreeting(?string $messageGreeting): void
    {
        $this->messageGreeting = $messageGreeting;
    }

    /**
     * @return string|null
     */
    public function getMessageSignature(): ?string
    {
        return $this->messageSignature;
    }

    /**
     * @param string|null $messageSignature
     */
    public function setMessageSignature(?string $messageSignature): void
    {
        $this->messageSignature = $messageSignature;
    }

    /**
     * @return Collection|WorkCategory[]
     */
    public function getWorkCategoriesOwner(): Collection
    {
        return $this->workCategoriesOwner;
    }

    /**
     * @param Collection $workCategoriesOwner
     */
    public function setWorkCategoriesOwner(Collection $workCategoriesOwner): void
    {
        $this->workCategoriesOwner = $workCategoriesOwner;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasksOwner(): Collection
    {
        return $this->tasksOwner;
    }

    /**
     * @param Collection $tasksOwner
     */
    public function setTasksOwner(Collection $tasksOwner): void
    {
        $this->tasksOwner = $tasksOwner;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMediaOwner(): Collection
    {
        return $this->mediaOwner;
    }

    /**
     * @param Collection $mediaOwner
     */
    public function setMediaOwner(Collection $mediaOwner): void
    {
        $this->mediaOwner = $mediaOwner;
    }

    /**
     * @return Collection|MediaCategory[]
     */
    public function getMediaCategoriesOwner(): Collection
    {
        return $this->mediaCategoriesOwner;
    }

    /**
     * @param Collection $mediaCategoriesOwner
     */
    public function setMediaCategoriesOwner(Collection $mediaCategoriesOwner): void
    {
        $this->mediaCategoriesOwner = $mediaCategoriesOwner;
    }

    /**
     * @return Collection|Work[]
     */
    public function getAuthorWorks(): Collection
    {
        return $this->authorWorks;
    }

    /**
     * @param mixed $authorWorks
     */
    public function setAuthorWorks($authorWorks): void
    {
        $this->authorWorks = $authorWorks;
    }

    /**
     * @return Collection|EventAddress[]
     */
    public function getEventAddressOwner(): Collection
    {
        return $this->eventAddressOwner;
    }

    /**
     * @param Collection $eventAddressOwner
     */
    public function setEventAddressOwner(Collection $eventAddressOwner): void
    {
        $this->eventAddressOwner = $eventAddressOwner;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEventsOwner(): Collection
    {
        return $this->eventsOwner;
    }

    /**
     * @param Collection $eventsOwner
     */
    public function setEventsOwner(Collection $eventsOwner): void
    {
        $this->eventsOwner = $eventsOwner;
    }

    /**
     * @return Collection|EventParticipant[]
     */
    public function getEventsParticipant(): Collection
    {
        return $this->eventsParticipant;
    }

    /**
     * @param Collection $eventsParticipant
     */
    public function setEventsParticipant(Collection $eventsParticipant): void
    {
        $this->eventsParticipant = $eventsParticipant;
    }

    /**
     * @return Collection|EventParticipant[]
     */
    public function getEventByParticipant(): Collection
    {
        $event = new ArrayCollection();

        /** @var EventParticipant $eventParticipant */
        foreach ($this->getEventsParticipant() as $eventParticipant) {
            $event->add($eventParticipant->getEvent());
        }

        return $event;
    }

    /**
     * @return Collection|EventSchedule[]
     */
    public function getEventsSchedule(): Collection
    {
        return $this->eventsSchedule;
    }

    /**
     * @param Collection $eventsSchedule
     */
    public function setEventsSchedule(Collection $eventsSchedule): void
    {
        $this->eventsSchedule = $eventsSchedule;
    }

    /**
     * @return Collection|Work[]
     */
    public function getOpponentWorks(): Collection
    {
        return $this->opponentWorks;
    }

    /**
     * @param mixed $opponentWorks
     */
    public function setOpponentWorks($opponentWorks): void
    {
        $this->opponentWorks = $opponentWorks;
    }

    /**
     * @return Collection|Work[]
     */
    public function getSupervisorWorks(): Collection
    {
        return $this->supervisorWorks;
    }

    /**
     * @param mixed $supervisorWorks
     */
    public function setSupervisorWorks($supervisorWorks): void
    {
        $this->supervisorWorks = $supervisorWorks;
    }

    /**
     * @return Collection|Work[]
     */
    public function getConsultantWorks(): Collection
    {
        return $this->consultantWorks;
    }

    /**
     * @param $consultantWorks
     */
    public function setConsultantWorks($consultantWorks): void
    {
        $this->consultantWorks = $consultantWorks;
    }

    /**
     * @return Collection|SystemEvent[]
     */
    public function getSystemEventsOwner(): Collection
    {
        return $this->systemEventsOwner;
    }

    /**
     * @param mixed $systemEventsOwner
     */
    public function setSystemEventsOwner($systemEventsOwner): void
    {
        $this->systemEventsOwner = $systemEventsOwner;
    }

    /**
     * @return Collection|SystemEventRecipient[]
     */
    public function getSystemEventsRecipient(): Collection
    {
        return $this->systemEventsRecipient;
    }

    /**
     * @param mixed $systemEventsRecipient
     */
    public function setSystemEventsRecipient($systemEventsRecipient): void
    {
        $this->systemEventsRecipient = $systemEventsRecipient;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getConversationsOwner(): Collection
    {
        return $this->conversationsOwner;
    }

    /**
     * @param Collection $conversationsOwner
     */
    public function setConversationsOwner(Collection $conversationsOwner): void
    {
        $this->conversationsOwner = $conversationsOwner;
    }

    /**
     * @return Collection|ConversationParticipant[]
     */
    public function getConversationsParticipant(): Collection
    {
        return $this->conversationsParticipant;
    }

    /**
     * @param Collection $conversationsParticipant
     */
    public function setConversationsParticipant(Collection $conversationsParticipant): void
    {
        $this->conversationsParticipant = $conversationsParticipant;
    }

    /**
     * @return Collection|ConversationMessage[]
     */
    public function getConversationMessages(): Collection
    {
        return $this->conversationMessages;
    }

    /**
     * @param Collection $conversationMessages
     */
    public function setConversationMessages(Collection $conversationMessages): void
    {
        $this->conversationMessages = $conversationMessages;
    }

    /**
     * @return Collection|ConversationMessageStatus[]
     */
    public function getConversationMessageStatus(): Collection
    {
        return $this->conversationMessageStatus;
    }

    /**
     * @param Collection $conversationMessageStatus
     */
    public function setConversationMessageStatus(Collection $conversationMessageStatus): void
    {
        $this->conversationMessageStatus = $conversationMessageStatus;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @param Collection $comments
     */
    public function setComments(Collection $comments): void
    {
        $this->comments = $comments;
    }

    /**
     * @return string|null
     */
    public function getSkype(): ?string
    {
        return $this->skype;
    }

    /**
     * @param string|null $skype
     */
    public function setSkype(?string $skype): void
    {
        $this->skype = $skype;
    }

    /**
     * @return Collection|Group[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroups($groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRoleConstant::ADMIN);
    }

    /**
     * @return bool
     */
    public function isAuthorSupervisorOpponent(): bool
    {
        return $this->isAuthor() || $this->isSupervisor() || $this->isOpponent();
    }

    /**
     * @return bool
     */
    public function isAuthorSupervisorOpponentConsultant(): bool
    {
        return $this->isAuthor() || $this->isSupervisor() || $this->isOpponent() || $this->isConsultant();
    }

    /**
     * @return bool
     */
    public function isAuthorSupervisor(): bool
    {
        return $this->isAuthor() || $this->isSupervisor();
    }

    /**
     * @return bool
     */
    public function isAuthor(): bool
    {
        return $this->hasRole(UserRoleConstant::STUDENT);
    }

    /**
     * @return bool
     */
    public function isSupervisor(): bool
    {
        return $this->hasRole(UserRoleConstant::SUPERVISOR);
    }

    /**
     * @return bool
     */
    public function isOpponent(): bool
    {
        return $this->hasRole(UserRoleConstant::OPPONENT);
    }

    /**
     * @return bool
     */
    public function isConsultant(): bool
    {
        return $this->hasRole(UserRoleConstant::CONSULTANT);
    }

    /**
     * @param string $userType
     * @return ArrayCollection
     */
    public function getActiveSupervisor(string $userType): ArrayCollection
    {
        $supervisors = new ArrayCollection();
        $userWorks = [];

        switch ($userType) {
            case WorkUserTypeConstant::AUTHOR:
                $userWorks = $this->arrayGenerator($this->getAuthorWorks());
                break;
            case WorkUserTypeConstant::OPPONENT:
                $userWorks = $this->arrayGenerator($this->getOpponentWorks());
                break;
            case WorkUserTypeConstant::CONSULTANT:
                $userWorks = $this->arrayGenerator($this->getConsultantWorks());
                break;
        }

        foreach ($userWorks as $work) {
            /** @var Work $work */
            if ($work->getStatus()->getId() === WorkStatusConstant::ACTIVE) {
                /** @var User|null $supervisor */
                $supervisor = $work->getSupervisor();
                if ($supervisor !== null &&
                    $supervisors->contains($supervisor) === false &&
                    $supervisor->isEnabled()
                ) {
                    $supervisors->add($supervisor);
                }
            }
        }

        return $supervisors;
    }

    /**
     * @param string $userType
     * @return ArrayCollection
     */
    public function getActiveAuthor(string $userType): ArrayCollection
    {
        $authors = new ArrayCollection();
        $userWorks = [];

        switch ($userType) {
            case WorkUserTypeConstant::OPPONENT:
                $userWorks = $this->arrayGenerator($this->getOpponentWorks());
                break;
            case WorkUserTypeConstant::SUPERVISOR:
                $userWorks = $this->arrayGenerator($this->getSupervisorWorks());
                break;
            case WorkUserTypeConstant::CONSULTANT:
                $userWorks = $this->arrayGenerator($this->getConsultantWorks());
                break;
        }

        foreach ($userWorks as $work) {
            /** @var Work $work */
            if ($work->getStatus()->getId() === WorkStatusConstant::ACTIVE) {
                /** @var User|null $supervisor */
                $author = $work->getAuthor();
                if ($author !== null &&
                    $authors->contains($author) === false &&
                    $author->isEnabled()
                ) {
                    $authors->add($author);
                }
            }
        }

        return $authors;
    }

    /**
     * @param string $userType
     * @return ArrayCollection
     */
    public function getActiveOpponent(string $userType): ArrayCollection
    {
        $opponents = new ArrayCollection;
        $userWorks = [];

        switch ($userType) {
            case WorkUserTypeConstant::AUTHOR:
                $userWorks = $this->arrayGenerator($this->getAuthorWorks());
                break;
            case WorkUserTypeConstant::SUPERVISOR:
                $userWorks = $this->arrayGenerator($this->getSupervisorWorks());
                break;
            case WorkUserTypeConstant::CONSULTANT:
                $userWorks = $this->arrayGenerator($this->getConsultantWorks());
                break;
        }

        foreach ($userWorks as $work) {
            /** @var Work $work */
            if ($work->getStatus()->getId() === WorkStatusConstant::ACTIVE) {
                /** @var User|null $supervisor */
                $opponent = $work->getOpponent();
                if ($opponent !== null &&
                    $opponents->contains($opponent) === false &&
                    $opponent->isEnabled()
                ) {
                    $opponents->add($opponent);
                }
            }
        }

        return $opponents;
    }

    /**
     * @param string $userType
     * @return ArrayCollection
     */
    public function getActiveConsultant(string $userType): ArrayCollection
    {
        $consultants = new ArrayCollection;
        $userWorks = [];

        switch ($userType) {
            case WorkUserTypeConstant::CONSULTANT:
                $userWorks = $this->arrayGenerator($this->getAuthorWorks());
                break;
            case WorkUserTypeConstant::SUPERVISOR:
                $userWorks = $this->arrayGenerator($this->getSupervisorWorks());
                break;
            case WorkUserTypeConstant::OPPONENT:
                $userWorks = $this->arrayGenerator($this->getOpponentWorks());
                break;
        }

        foreach ($userWorks as $work) {
            /** @var Work $work */
            if ($work->getStatus()->getId() === WorkStatusConstant::ACTIVE) {
                /** @var User|null $consultant */
                $consultant = $work->getConsultant();
                if ($consultant !== null &&
                    $consultants->contains($consultant) === false &&
                    $consultant->isEnabled()
                ) {
                    $consultants->add($consultant);
                }
            }
        }

        return $consultants;
    }

    /**
     * @param string $userType
     * @param null $type
     * @param null $status
     * @return ArrayCollection
     */
    public function getWorkBy(
        string $userType,
        $type = null,
        $status = null
    ): ArrayCollection {
        $collectionWorks = new ArrayCollection;
        $userWorks = new ArrayCollection;
        $criteria = Criteria::create();

        switch ($userType) {
            case WorkUserTypeConstant::AUTHOR:
                $userWorks = $this->getAuthorWorks();
                break;
            case WorkUserTypeConstant::SUPERVISOR:
                $userWorks = $this->getSupervisorWorks();
                break;
            case WorkUserTypeConstant::OPPONENT:
                $userWorks = $this->getOpponentWorks();
                break;
            case WorkUserTypeConstant::CONSULTANT:
                $userWorks = $this->getConsultantWorks();
                break;
        }

        if ($type !== null) {
            $criteria->where(Criteria::expr()->eq('type', $type));
            $userWorks = $userWorks->matching($criteria);
        }

        if ($status !== null) {
            $criteria->andWhere(Criteria::expr()->eq('status', $status));
            $userWorks = $userWorks->matching($criteria);
        }

        if (!$userWorks->isEmpty()) {
            foreach ($userWorks as $work) {
                $collectionWorks->add($work);
            }
        }

        return $userWorks;
    }

    /**
     * @param Collection $array
     * @return Generator
     */
    public function arrayGenerator(Collection $array): Generator
    {
        yield from $array;
    }

    /**
     * @param MediaType|null $type
     * @param bool|null $active
     * @return ArrayCollection
     */
    public function getMediaBy(?MediaType $type = null, ?bool $active = null): ArrayCollection
    {
        $collectionMedias = new ArrayCollection;
        $criteria = Criteria::create();

        $userMedia = $this->getMediaOwner();

        if ($type !== null) {
            $criteria->where(Criteria::expr()->eq('type', $type));
            $userMedia = $userMedia->matching($criteria);
        }

        if ($active !== null) {
            $criteria->where(Criteria::expr()->eq('active', $active));
            $userMedia = $userMedia->matching($criteria);
        }

        if (!$userMedia->isEmpty()) {
            foreach ($userMedia as $media) {
                $collectionMedias->add($media);
            }
        }

        return $collectionMedias;
    }

    /**
     * @return Media|null
     */
    public function getProfileImage(): ?Media
    {
        return $this->profileImage;
    }

    /**
     * @param Media $profileImage
     */
    public function setProfileImage(Media $profileImage): void
    {
        $this->profileImage = $profileImage;
    }

    /**
     * @return string
     */
    public function getProfileImagePath(): ?string
    {
        if ($this->profileImage !== null) {
            return $this->profileImage->getWebPath();
        }

        return 'images/user.png';
    }

    /**
     * @return string
     */
    public function getMessageHeaderFooter(): string
    {
        $greeting = $this->getMessageGreeting() ?? '';
        $signature = $this->getMessageSignature() ?? '';

        return sprintf("%s\n%s", $greeting, $signature);
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return sprintf('%s %s', $this->getLastname(), $this->getFirstname());
    }

    /**
     * @return string
     */
    public function getFullNameDegree(): string
    {
        $before = $this->getDegreeBefore() ?? '';
        $after = $this->getDegreeAfter() ?? '';

        return sprintf('%s %s %s', $this->getFullName(), $before, $after);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFullNameDegree();
    }
}
