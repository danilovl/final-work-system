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
    ArrayCollection,
    Collection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @ORM\Table(name="event_schedule")
 * @ORM\Entity(repositoryClass="App\Repository\EventScheduleRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class EventSchedule
{
    use IdTrait;
    use SimpleInformationTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="eventsSchedule", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?User $owner = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EventScheduleTemplate", mappedBy="schedule", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $templates = null;

    public function __construct()
    {
        $this->templates = new ArrayCollection;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return Collection|EventScheduleTemplate[]
     */
    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    public function setTemplates(Collection $templates): void
    {
        $this->templates = $templates;
    }

    public function addTemplate(EventScheduleTemplate $template): void
    {
        $this->templates->add($template);
        $template->setSchedule($this);
    }

    public function removeTemplate(EventScheduleTemplate $template): void
    {
        $this->templates->removeElement($template);
    }

    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
