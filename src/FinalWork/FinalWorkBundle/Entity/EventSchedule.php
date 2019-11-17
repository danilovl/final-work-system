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

use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @ORM\Table(name="event_schedule")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\EventScheduleRepository")
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
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="eventsSchedule", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var Collection|EventScheduleTemplate[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\EventScheduleTemplate", mappedBy="schedule", cascade={"persist", "remove"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $templates;

    /**
     * EventSchedule constructor.
     */
    public function __construct()
    {
        $this->templates = new ArrayCollection;
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
     * @return Collection|EventScheduleTemplate[]
     */
    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    /**
     * @param Collection $templates
     */
    public function setTemplates(Collection $templates): void
    {
        $this->templates = $templates;
    }

    /**
     * @param EventScheduleTemplate $template
     */
    public function addTemplate(EventScheduleTemplate $template): void
    {
        $this->templates->add($template);
        $template->setSchedule($this);
    }

    /**
     * @param EventScheduleTemplate $template
     */
    public function removeTemplate(EventScheduleTemplate $template): void
    {
        $this->templates->removeElement($template);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
