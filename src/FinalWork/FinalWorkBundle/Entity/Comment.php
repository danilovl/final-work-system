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
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\CommentRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class Comment
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var Event|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Event", inversedBy="comment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $event;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     * @Gedmo\Versioned
     */
    private $content;

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
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
     * @return Event|null
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getContent() ?: TranslationConstant::EMPTY;
    }
}
