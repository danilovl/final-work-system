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
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="article")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\ArticleRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class Article
{
    use IdTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", nullable=false)
     * @Gedmo\Versioned
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     * @Gedmo\Versioned
     */
    private $content;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="comments", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var Collection|ArticleCategory[]
     *
     * @ORM\ManyToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\ArticleCategory", inversedBy="articles", fetch="EAGER")
     * @ORM\JoinTable(name="article_to_article_category",
     *      joinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="article_category_id", nullable=false, referencedColumnName="id")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $categories;

    /**
     * Article constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

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
     * @return Collection|ArticleCategory[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param Collection $categories
     */
    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
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
     * @return string
     */
    public function __toString(): string
    {
        return $this->getTitle() ?: TranslationConstant::EMPTY;
    }
}
