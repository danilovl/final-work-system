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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use App\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="article")
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
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
     * @ORM\Column(name="title", type="string", nullable=false)
     * @Gedmo\Versioned
     */
    private ?string $title = null;

    /**
     * @ORM\Column(name="content", type="text", nullable=false)
     * @Gedmo\Versioned
     */
    private ?string $content = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?User $owner = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ArticleCategory", inversedBy="articles", fetch="EAGER")
     * @ORM\JoinTable(name="article_to_article_category",
     *      joinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="article_category_id", nullable=false, referencedColumnName="id")
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $categories = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

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

    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    public function __toString(): string
    {
        return $this->getTitle() ?: TranslationConstant::EMPTY;
    }
}
