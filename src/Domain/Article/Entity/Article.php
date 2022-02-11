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

namespace App\Domain\Article\Entity;

use App\Application\Constant\TranslationConstant;
use App\Application\Traits\Entity\{
    IdTrait,
    IsOwnerTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait
};
use App\Domain\Article\Repository\ArticleRepository;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'article')]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable]
class Article
{
    use IdTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    #[ORM\Column(name: 'title', type: Types::STRING, nullable: false)]
    #[Gedmo\Versioned]
    private ?string $title = null;

    #[ORM\Column(name: 'content', type: Types::TEXT, nullable: false)]
    #[Gedmo\Versioned]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER', inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: ArticleCategory::class, inversedBy: 'articles', fetch: 'EAGER')]
    #[ORM\JoinTable(name: 'article_to_article_category')]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'article_category_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $categories;

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
