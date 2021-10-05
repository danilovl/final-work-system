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

use App\Repository\EventAddressRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    LocationTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @Gedmo\Loggable
 */
#[ORM\Table(name: 'event_address')]
#[ORM\Entity(repositoryClass: EventAddressRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class EventAddress
{
    use IdTrait;
    use SimpleInformationTrait;
    use LocationTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'eventAddressOwner')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $owner = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'street', type: Types::STRING, nullable: true)]
    private ?string $street = null;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'skype', type: Types::BOOLEAN, options: ['default' => '0'])]
    private bool $skype = false;

    #[ORM\OneToMany(mappedBy: 'address', targetEntity: Event::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    public function isSkype(): bool
    {
        return $this->skype;
    }

    public function setSkype(bool $skype): void
    {
        $this->skype = $skype;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function setEvents(Collection $events): void
    {
        $this->events = $events;
    }

    public function existLocation(): bool
    {
        return $this->getLatitude() && $this->getLatitude();
    }

    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
