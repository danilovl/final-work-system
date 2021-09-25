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

use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    ConstantAwareTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @Gedmo\Loggable
 */
#[ORM\Table(name: 'system_event_type')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class SystemEventType
{
    use IdTrait;
    use SimpleInformationTrait;
    use ConstantAwareTrait;
    use CreateUpdateAbleTrait;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'group', type: Types::STRING, nullable: false)]
    private ?string $group = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: SystemEvent::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $systemEvents;

    public function __construct()
    {
        $this->systemEvents = new ArrayCollection;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * @return Collection|SystemEvent[]
     */
    public function getSystemEvents(): Collection
    {
        return $this->systemEvents;
    }

    public function setSystemEvents(Collection $systemEvents): void
    {
        $this->systemEvents = $systemEvents;
    }

    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
