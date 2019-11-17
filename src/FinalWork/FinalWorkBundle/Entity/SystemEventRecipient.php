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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};
use FinalWork\SonataUserBundle\Entity\User;

/**
 * @ORM\Table(name="system_event_recipient")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\SystemEventRecipientRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class SystemEventRecipient
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var SystemEvent|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\SystemEvent", inversedBy="recipient", fetch="EAGER")
     * @ORM\JoinColumn(name="system_event_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $systemEvent;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="systemEventsRecipient", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $recipient;

    /**
     * @var bool
     *
     * @ORM\Column(name="viewed", type="boolean", options={"default":"0"})
     * @Gedmo\Versioned
     */
    private $viewed = false;

    /**
     * @return SystemEvent|null
     */
    public function getSystemEvent(): ?SystemEvent
    {
        return $this->systemEvent;
    }

    /**
     * @param SystemEvent $systemEvent
     */
    public function setSystemEvent(SystemEvent $systemEvent): void
    {
        $this->systemEvent = $systemEvent;
    }

    /**
     * @return User|null
     */
    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    /**
     * @param User $recipient
     */
    public function setRecipient(User $recipient): void
    {
        $this->recipient = $recipient;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isRecipient(User $user): bool
    {
        return $this->getRecipient()->getId() === $user->getId();
    }

    /**
     * @return bool
     */
    public function isViewed(): bool
    {
        return $this->viewed;
    }

    /**
     * @param bool $viewed
     */
    public function setViewed(bool $viewed): void
    {
        $this->viewed = $viewed;
    }

    /**
     * @return void
     */
    public function changeViewed(): void
    {
        if ($this->isViewed()) {
            $this->setViewed(false);
        } else {
            $this->setViewed(true);
        }
    }
}
