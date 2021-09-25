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

use App\Repository\ArticleCategoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @Gedmo\Loggable
 */
#[ORM\Table(name: 'article_category')]
#[ORM\Entity(repositoryClass: ArticleCategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class ArticleCategory
{
    use IdTrait;
    use SimpleInformationTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'access', type: Types::ARRAY, nullable: false)]
    private ?array $access = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'workCategoriesOwner')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'categories')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection;
    }

    public function getAccess(): ?array
    {
        return $this->access;
    }

    public function setAccess(array $roles): self
    {
        $this->access = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function addRole(string $role): self
    {
        $role = strtoupper($role);

        if (!in_array($role, $this->getAccess(), true)) {
            $this->access[] = $role;
        }

        return $this;
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
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function setArticles(Collection $articles): void
    {
        $this->articles = $articles;
    }

    public function isOwner(User $user): bool
    {
        return $this->getOwner()->getId() === $user->getId();
    }

    public function hasAccess(string $role): bool
    {
        return in_array(strtoupper($role), $this->access, true);
    }

    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
