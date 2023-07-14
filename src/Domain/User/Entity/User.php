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

namespace App\Domain\User\Entity;

use App\Application\Constant\{
    GenderConstant};
use App\Application\Traits\Entity\TimestampAbleTrait;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\Event\Entity\Event;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\Media\Entity\Media;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\Task\Entity\Task;
use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\Repository\UserRepository;
use App\Domain\UserGroup\Entity\Group;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkCategory\Entity\WorkCategory;
use DateTime;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection,
    Criteria};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\{
    LegacyPasswordAuthenticatedUserInterface,
    PasswordAuthenticatedUserInterface,
    UserInterface};

#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['email'])]
#[UniqueEntity(fields: ['username'])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, LegacyPasswordAuthenticatedUserInterface
{
    use TimestampAbleTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'username', type: Types::STRING, length: 180, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(name: 'roles', type: Types::ARRAY)]
    private array $roles;

    #[ORM\Column(name: 'password', type: Types::STRING)]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column(name: 'last_login', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $lastLogin = null;

    #[ORM\Column(name: 'last_requested_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $lastRequestedAt = null;

    #[ORM\Column(name: 'confirmation_token', type: Types::STRING, nullable: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(name: 'password_requested_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $passwordRequestedAt = null;

    #[ORM\Column(name: 'username_canonical', type: Types::STRING, nullable: true)]
    private ?string $usernameCanonical = null;

    #[ORM\Column(name: 'email', type: Types::STRING, unique: true, nullable: false)]
    private string $email;

    #[ORM\Column(name: 'email_canonical', type: Types::STRING, nullable: false)]
    private string $emailCanonical;

    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, options: ['default' => '0'])]
    private bool $enabled = false;

    #[ORM\Column(name: 'enabled_email_notification', type: Types::BOOLEAN, options: ['default' => '1'])]
    private bool $enabledEmailNotification = true;

    #[ORM\Column(name: 'date_of_birth', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $dateOfBirth = null;

    #[ORM\Column(name: 'firstname', type: Types::STRING, nullable: false)]
    private string $firstname;

    #[ORM\Column(name: 'lastname', type: Types::STRING, nullable: false)]
    private string $lastname;

    #[ORM\Column(name: 'website', type: Types::STRING, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(name: 'biography', type: Types::STRING, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(name: 'gender', type: Types::STRING, nullable: false)]
    private string $gender = GenderConstant::UNKNOWN->value;

    #[ORM\Column(name: 'locale', type: Types::STRING, nullable: true)]
    private ?string $locale = null;

    #[ORM\Column(name: 'timezone', type: Types::STRING, nullable: true)]
    private ?string $timezone = null;

    #[ORM\Column(name: 'phone', type: Types::STRING, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(name: 'token', type: Types::STRING, length: 100, unique: true, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(name: 'salt', type: Types::STRING, nullable: true)]
    private ?string $salt = null;

    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_to_user_group')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'group_id', referencedColumnName: 'id')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $groups;

    #[ORM\OneToOne(targetEntity: Media::class, cascade: ['persist', 'remove'], fetch: "EAGER")]
    #[ORM\JoinColumn(name: 'profile_image_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Media $profileImage = null;

    #[ORM\Column(name: 'skype', type: Types::STRING, nullable: true)]
    private ?string $skype = null;

    #[ORM\Column(name: 'degree_before', type: Types::STRING, nullable: true)]
    private ?string $degreeBefore = null;

    #[ORM\Column(name: 'degree_after', type: Types::STRING, nullable: true)]
    private ?string $degreeAfter = null;

    #[ORM\Column(name: 'message_greeting', type: Types::STRING, nullable: true)]
    private ?string $messageGreeting = null;

    #[ORM\Column(name: 'message_signature', type: Types::STRING, nullable: true)]
    private ?string $messageSignature = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Media::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $mediaOwner;

    /**
     * @var Collection|MediaCategory[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: MediaCategory::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $mediaCategoriesOwner;

    /**
     * @var Collection|Work[]
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Work::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $authorWorks;

    /**
     * @var Collection|Work[]
     */
    #[ORM\OneToMany(mappedBy: 'supervisor', targetEntity: Work::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $supervisorWorks;

    /**
     * @var Collection|Work[]
     */
    #[ORM\OneToMany(mappedBy: 'opponent', targetEntity: Work::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $opponentWorks;

    /**
     * @var Collection|Work[]
     */
    #[ORM\OneToMany(mappedBy: 'consultant', targetEntity: Work::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $consultantWorks;

    /**
     * @var Collection|Task[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Task::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $tasksOwner;

    /**
     * @var Collection|WorkCategory[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: WorkCategory::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $workCategoriesOwner;

    /**
     * @var Collection|EventAddress[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: EventAddress::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $eventAddressOwner;

    /**
     * @var Collection|Event[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Event::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $eventsOwner;

    /**
     * @var Collection|EventParticipant[]
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: EventParticipant::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $eventsParticipant;

    /**
     * @var Collection|Conversation[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Conversation::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $conversationsOwner;

    /**
     * @var Collection|ConversationParticipant[]
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ConversationParticipant::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $conversationsParticipant;

    /**
     * @var Collection|ConversationMessage[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: ConversationMessage::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $conversationMessages;

    /**
     * @var Collection|ConversationMessageStatus[]
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ConversationMessageStatus::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $conversationMessageStatus;

    /**
     * @var Collection|Comment[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $comments;

    /**
     * @var Collection|EventSchedule[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: EventSchedule::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $eventsSchedule;

    /**
     * @var Collection|SystemEvent[]
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: SystemEvent::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $systemEventsOwner;

    /**
     * @var Collection|SystemEventRecipient[]
     */
    #[ORM\OneToMany(mappedBy: 'recipient', targetEntity: SystemEventRecipient::class, cascade: ['persist', 'remove'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $systemEventsRecipient;

    private array $additionRoles = [];

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

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
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

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        $roles = array_merge($roles, $this->additionRoles);

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addAdditionRole(string $role): void
    {
        $this->additionRoles[] = $role;
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

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(?string $salt): void
    {
        $this->salt = $salt;
    }

    public function eraseCredentials(): void
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

    public function updateLastLogin(): void
    {
        $this->lastLogin = new DateTime;
    }

    public function getLastRequestedAt(): ?DateTime
    {
        return $this->lastRequestedAt;
    }

    public function setLastRequestedAt(?DateTime $lastRequestedAt): void
    {
        $this->lastRequestedAt = $lastRequestedAt;
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
     * @return Collection<Media>
     */
    public function getMediaOwner(): Collection
    {
        return $this->mediaOwner;
    }

    /**
     * @return Collection<Work>
     */
    public function getAuthorWorks(): Collection
    {
        return $this->authorWorks;
    }

    public function setAuthorWorks(Collection $authorWorks): void
    {
        $this->authorWorks = $authorWorks;
    }

    /**
     * @return Collection<EventAddress>
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
     * @return Collection<Event>
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
     * @return Collection<EventParticipant>
     */
    public function getEventsParticipant(): Collection
    {
        return $this->eventsParticipant;
    }

    /**
     * @return Collection<EventParticipant>
     */
    public function getEventByParticipant(): Collection
    {
        $event = new ArrayCollection;

        /** @var EventParticipant $eventParticipant */
        foreach ($this->getEventsParticipant() as $eventParticipant) {
            $event->add($eventParticipant->getEvent());
        }

        return $event;
    }

    /**
     * @return Collection<Work>
     */
    public function getOpponentWorks(): Collection
    {
        return $this->opponentWorks;
    }

    /**
     * @return Collection<Work>
     */
    public function getSupervisorWorks(): Collection
    {
        return $this->supervisorWorks;
    }

    /**
     * @return Collection<Work>
     */
    public function getConsultantWorks(): Collection
    {
        return $this->consultantWorks;
    }

    /**
     * @return Collection<Comment>
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
     * @return Collection<Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    public function addGroups(Group $group): void
    {
        $this->groups->add($group);
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
        $titles = '';

        if (!empty($this->getDegreeBefore())) {
            $titles .= ' ' . $this->getDegreeBefore();
        }

        if (!empty($this->getDegreeAfter())) {
            $titles .= ' ' . $this->getDegreeAfter();
        }

        return sprintf('%s%s', $this->getFullName(), $titles);
    }

    public function addRole(string $role): self
    {
        $role = strtoupper($role);
        if ($role === UserRoleConstant::USER->value) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function isOnline(): bool
    {
        if ($this->lastRequestedAt === null) {
            return false;
        }

        return (clone $this->lastRequestedAt)->modify('+5 min') > new DateTime;
    }

    public function serialize(): string
    {
        return serialize([
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical,
        ]);
    }

    public function __toString(): string
    {
        return $this->getFullNameDegree();
    }
}
