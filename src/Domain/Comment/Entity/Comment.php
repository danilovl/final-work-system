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

namespace App\Domain\Comment\Entity;

use App\Application\Constant\TranslationConstant;
use App\Application\Traits\Entity\{
    IdTrait,
    CreateUpdateAbleTrait
};
use App\Domain\Comment\Repository\CommentRepository;
use App\Domain\Event\Entity\Event;
use App\Domain\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'comment')]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class Comment
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'comment')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?\App\Domain\Event\Entity\Event $event = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?\App\Domain\User\Entity\User $owner = null;

    #[ORM\Column(name: 'content', type: Types::TEXT, nullable: false)]
    #[Gedmo\Versioned]
    private ?string $content = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(\App\Domain\User\Entity\User $owner): void
    {
        $this->owner = $owner;
    }

    public function getEvent(): ?\App\Domain\Event\Entity\Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function __toString(): string
    {
        return $this->getContent() ?: TranslationConstant::EMPTY;
    }
}
