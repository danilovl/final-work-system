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

use App\Constant\{
    GenderConstant,
    TranslationConstant,
    UserRoleConstant,
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Entity\Traits\TimestampAbleTrait;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\{
    Criteria,
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use Generator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="user")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface
{
    use TimestampAbleTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="username", type="string", length=180, unique=true)
     */
    private ?string $username = null;

    /**
     * @ORM\Column(name="roles", type="array")
     */
    private array $roles = [];

    /**
     * @ORM\Column(name="password", type="string")
     */
    private ?string $password = null;

    private ?string $plainPassword = null;

    /**
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private ?DateTime $lastLogin = null;

    /**
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     */
    private ?string $confirmationToken = null;

    /**
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    private ?DateTime $passwordRequestedAt = null;

    /**
     * @ORM\Column(name="username_canonical", type="string", nullable=true)
     */
    private ?string $usernameCanonical = null;

    /**
     * @ORM\Column(name="email", type="string", nullable=false)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(name="email_canonical", type="string", nullable=false)
     */
    private ?string $emailCanonical = null;

    /**
     * @ORM\Column(name="enabled", type="boolean", options={"default":"0"})
     */
    private bool $enabled = false;

    /**
     * @ORM\Column(name="enabled_email_notification", type="boolean", options={"default":"1"})
     */
    private bool $enabledEmailNotification = true;

    /**
     * @ORM\Column(name="date_of_birth", type="datetime", nullable=true)
     */
    private ?DateTime $dateOfBirth = null;

    /**
     * @ORM\Column(name="firstname", type="string")
     */
    private ?string $firstname = null;

    /**
     * @ORM\Column(name="lastname", type="string")
     */
    private ?string $lastname = null;

    /**
     * @ORM\Column(name="website", type="string", nullable=true)
     */
    private ?string $website = null;

    /**
     * @ORM\Column(name="biography", type="string", nullable=true)
     */
    private ?string $biography = null;

    /**
     * @ORM\Column(name="gender", type="string")
     */
    private ?string $gender = GenderConstant::GENDER_UNKNOWN;

    /**
     * @ORM\Column(name="locale", type="string", nullable=true)
     */
    private ?string $locale = null;

    /**
     * @ORM\Column(name="timezone", type="string", nullable=true)
     */
    private ?string $timezone = null;

    /**
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    private ?string $phone = null;

    /**
     * @ORM\Column(name="token", type="string", nullable=true)
     */
    private ?string $token = null;

    /**
     * @ORM\Column(name="salt", type="string")
     */
    private ?string $salt = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Group", inversedBy="users")
     * @ORM\JoinTable(name="user_to_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $groups = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Media", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="profile_image_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Media $profileImage = null;

    /**
     * @ORM\Column(name="skype", type="string", nullable=true)
     */
    private ?string $skype = null;

    /**
     * @ORM\Column(name="degree_before", type="string", nullable=true)
     */
    private ?string $degreeBefore = null;

    /**
     * @ORM\Column(name="degree_after", type="string", nullable=true)
     */
    private ?string $degreeAfter = null;

    /**
     * @ORM\Column(name="message_greeting", type="string", nullable=true)
     */
    private ?string $messageGreeting = null;

    /**
     * @ORM\Column(name="message_signature", type="string", nullable=true)
     */
    private ?string $messageSignature = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $mediaOwner = null;

    /**
     * @var Collection|MediaCategory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\MediaCategory", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $mediaCategoriesOwner = null;

    /**
     * @var Collection|Work[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Work", mappedBy="author", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $authorWorks = null;

    /**
     * @var Collection|Work[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Work", mappedBy="supervisor", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $supervisorWorks = null;

    /**
     * @var Collection|Work[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Work", mappedBy="opponent", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $opponentWorks = null;

    /**
     * @var Collection|Work[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Work", mappedBy="consultant", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $consultantWorks = null;

    /**
     * @var Collection|Task[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $tasksOwner = null;

    /**
     * @var Collection|WorkCategory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\WorkCategory", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $workCategoriesOwner = null;

    /**
     * @var Collection|EventAddress[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventAddress", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $eventAddressOwner = null;

    /**
     * @var Collection|Event[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $eventsOwner = null;

    /**
     * @var Collection|EventParticipant[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventParticipant", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $eventsParticipant = null;

    /**
     * @var Collection|Conversation[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Conversation", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $conversationsOwner = null;

    /**
     * @var Collection|ConversationParticipant[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ConversationParticipant", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $conversationsParticipant = null;

    /**
     * @var Collection|ConversationMessage[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ConversationMessage", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $conversationMessages = null;

    /**
     * @var Collection|ConversationMessageStatus[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ConversationMessageStatus", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $conversationMessageStatus = null;

    /**
     * @var Collection|Comment[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $comments = null;

    /**
     * @var Collection|EventSchedule[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventSchedule", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $eventsSchedule = null;

    /**
     * @var Collection|SystemEvent[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\SystemEvent", mappedBy="owner", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $systemEventsOwner = null;

    /**
     * @var Collection|SystemEventRecipient[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\SystemEventRecipient", mappedBy="recipient", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $systemEventsRecipient = null;

    public function __construct()
    {
        $this->roles = [];
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

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): void
    {
        $this->biography = $biography;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): void
    {
        $this->timezone = $timezone;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function getPasswordRequestedAt(): ?DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?DateTime $passwordRequestedAt): void
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    }

    public function getUsernameCanonical(): ?string
    {
        return $this->usernameCanonical;
    }

    public function setUsernameCanonical(?string $usernameCanonical): void
    {
        $this->usernameCanonical = $usernameCanonical;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getEmailCanonical(): ?string
    {
        return $this->emailCanonical;
    }

    public function setEmailCanonical(?string $emailCanonical): void
    {
        $this->emailCanonical = $emailCanonical;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabledEmailNotification(): bool
    {
        return $this->enabledEmailNotification;
    }

    public function setEnabledEmailNotification(bool $enabledEmailNotification): void
    {
        $this->enabledEmailNotification = $enabledEmailNotification;
    }

    public function getDegreeBefore(): ?string
    {
        return $this->degreeBefore;
    }

    public function setDegreeBefore(?string $degreeBefore): void
    {
        $this->degreeBefore = $degreeBefore;
    }

    public function getDegreeAfter(): ?string
    {
        return $this->degreeAfter;
    }

    public function setDegreeAfter(?string $degreeAfter): void
    {
        $this->degreeAfter = $degreeAfter;
    }

    public function getMessageGreeting(): ?string
    {
        return $this->messageGreeting;
    }

    public function setMessageGreeting(?string $messageGreeting): void
    {
        $this->messageGreeting = $messageGreeting;
    }

    public function getMessageSignature(): ?string
    {
        return $this->messageSignature;
    }

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

    public function setComments(Collection $comments): void
    {
        $this->comments = $comments;
    }

    public function getSkype(): ?string
    {
        return $this->skype;
    }

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

    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    public function addGroups(Group $group)
    {
        $this->groups->add($group);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRoleConstant::ADMIN);
    }

    public function isAuthorSupervisorOpponent(): bool
    {
        return $this->isAuthor() || $this->isSupervisor() || $this->isOpponent();
    }

    public function isAuthorSupervisorOpponentConsultant(): bool
    {
        return $this->isAuthor() || $this->isSupervisor() || $this->isOpponent() || $this->isConsultant();
    }

    public function isAuthorSupervisor(): bool
    {
        return $this->isAuthor() || $this->isSupervisor();
    }

    public function isAuthor(): bool
    {
        return $this->hasRole(UserRoleConstant::STUDENT);
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole(UserRoleConstant::SUPERVISOR);
    }

    public function isOpponent(): bool
    {
        return $this->hasRole(UserRoleConstant::OPPONENT);
    }

    public function isConsultant(): bool
    {
        return $this->hasRole(UserRoleConstant::CONSULTANT);
    }

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
                /** @var \App\Entity\User|null $supervisor */
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

    public function arrayGenerator(Collection $array): Generator
    {
        yield from $array;
    }

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

    public function getProfileImage(): ?Media
    {
        return $this->profileImage;
    }

    public function setProfileImage(Media $profileImage): void
    {
        $this->profileImage = $profileImage;
    }

    public function getProfileImagePath(): ?string
    {
        if ($this->profileImage !== null) {
            return $this->profileImage->getWebPath();
        }

        return 'images/user.png';
    }

    public function getMessageHeaderFooter(): string
    {
        $greeting = $this->getMessageGreeting() ?? '';
        $signature = $this->getMessageSignature() ?? '';

        return sprintf("%s\n%s", $greeting, $signature);
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->getLastname(), $this->getFirstname());
    }

    public function getFullNameDegree(): string
    {
        $before = $this->getDegreeBefore() ?? '';
        $after = $this->getDegreeAfter() ?? '';

        return sprintf('%s %s %s', $this->getFullName(), $before, $after);
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === 'ROLE_USER') {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function serialize()
    {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical,
        ));
    }

    public function __toString(): string
    {
        return $this->getFullNameDegree() ?: TranslationConstant::EMPTY;
    }
}
