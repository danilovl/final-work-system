<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\WorkType\Entity;

use App\Application\Traits\Entity\{
    IdTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use App\Domain\Work\Entity\Work;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'work_type')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class WorkType
{
    use IdTrait;
    use SimpleInformationTrait;
    use CreateUpdateAbleTrait;

    #[ORM\Column(name: 'shortcut', type: Types::STRING, nullable: false)]
    #[Gedmo\Versioned]
    private string $shortcut;

    /** @var Collection<Work> */
    #[ORM\OneToMany(targetEntity: Work::class, mappedBy: 'type')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $works;

    public function __construct()
    {
        $this->works = new ArrayCollection;
    }

    public function getShortcut(): string
    {
        return $this->shortcut;
    }

    public function setShortcut(string $shortcut): void
    {
        $this->shortcut = $shortcut;
    }

    /**
     * @return Collection<Work>
     */
    public function getWorks(): Collection
    {
        return $this->works;
    }

    public function setWorks(Collection $works): void
    {
        $this->works = $works;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
