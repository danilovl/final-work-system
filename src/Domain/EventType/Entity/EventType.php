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

namespace App\Domain\EventType\Entity;

use App\Application\Constant\TranslationConstant;
use App\Application\Traits\Entity\{
    IdTrait,
    ConstantAwareTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use App\Domain\Event\Entity\Event;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'event_type')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class EventType
{
    use IdTrait;
    use SimpleInformationTrait;
    use ConstantAwareTrait;
    use CreateUpdateAbleTrait;

    #[ORM\Column(name: 'color', type: Types::STRING, nullable: false)]
    #[Gedmo\Versioned]
    private ?string $color = null;

    #[ORM\Column(name: 'registrable', type: Types::BOOLEAN, options: ['default' => '0'])]
    #[Gedmo\Versioned]
    private bool $registrable = false;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Event::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function isRegistrable(): ?bool
    {
        return $this->registrable;
    }

    public function setRegistrable(bool $registrable): void
    {
        $this->registrable = $registrable;
    }

    /**
     * @return Collection<Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function setEvents(Collection $events): void
    {
        $this->events = $events;
    }

    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
