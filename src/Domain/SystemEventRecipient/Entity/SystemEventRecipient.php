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

namespace App\Domain\SystemEventRecipient\Entity;

use App\Application\Traits\Entity\{
    IdTrait,
    CreateUpdateAbleTrait
};
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Repository\SystemEventRecipientRepository;
use App\Domain\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'system_event_recipient')]
#[ORM\Entity(repositoryClass: SystemEventRecipientRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class SystemEventRecipient
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    #[ORM\ManyToOne(targetEntity: SystemEvent::class, fetch: 'EAGER', inversedBy: 'recipient')]
    #[ORM\JoinColumn(name: 'system_event_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?SystemEvent $systemEvent = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'systemEventsRecipient')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $recipient = null;

    #[ORM\Column(name: 'viewed', type: Types::BOOLEAN, options: ['default' => '0'])]
    #[Gedmo\Versioned]
    private bool $viewed = false;

    public function getSystemEvent(): ?SystemEvent
    {
        return $this->systemEvent;
    }

    public function setSystemEvent(SystemEvent $systemEvent): void
    {
        $this->systemEvent = $systemEvent;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(User $recipient): void
    {
        $this->recipient = $recipient;
    }

    public function isRecipient(User $user): bool
    {
        return $this->getRecipient()->getId() === $user->getId();
    }

    public function isViewed(): bool
    {
        return $this->viewed;
    }

    public function setViewed(bool $viewed): void
    {
        $this->viewed = $viewed;
    }

    public function changeViewed(): void
    {
        if ($this->isViewed()) {
            $this->setViewed(false);
        } else {
            $this->setViewed(true);
        }
    }
}
