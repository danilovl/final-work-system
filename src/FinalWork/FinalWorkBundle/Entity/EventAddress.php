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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\SonataUserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    LocationTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @ORM\Table(name="event_address")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\EventAddressRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class EventAddress
{
    use IdTrait;
    use SimpleInformationTrait;
    use LocationTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="eventAddressOwner", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var string|null
     *
     * @ORM\Column(name="street", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private $street;

    /**
     * @var bool
     *
     * @ORM\Column(name="skype", type="boolean", options={"default":"0"})
     * @Gedmo\Versioned
     */
    private $skype = false;

    /**
     * @var Collection|Event[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Event", mappedBy="address")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $events;

    /**
     * Address constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
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
     * @return bool
     */
    public function isSkype(): ?bool
    {
        return $this->skype;
    }

    /**
     * @param bool $skype
     */
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

    /**
     * @param Collection $events
     */
    public function setEvents(Collection $events): void
    {
        $this->events = $events;
    }

    /**
     * @return bool
     */
    public function existLocation(): bool
    {
        return ($this->getLatitude() && $this->getLatitude());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
