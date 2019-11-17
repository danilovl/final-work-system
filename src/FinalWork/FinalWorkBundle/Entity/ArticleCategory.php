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
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use FinalWork\SonataUserBundle\Entity\User;

/**
 * @ORM\Table(name="article_category")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\ArticleCategoryRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
 * @Gedmo\Loggable
 */
class ArticleCategory
{
    use IdTrait;
    use SimpleInformationTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var array|null
     *
     * @ORM\Column(name="access", type="array", nullable=false))
     * @Gedmo\Versioned
     */
    private $access;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="workCategoriesOwner")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var Collection|Article[]
     *
     * @ORM\ManyToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Article", mappedBy="categories")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $articles;

    /**
     * WorkCategory constructor.
     */
    public function __construct()
    {
        $this->articles = new ArrayCollection;
    }

    /**
     * @return array|null
     */
    public function getAccess(): ?array
    {
        return $this->access;
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setAccess(array $roles): self
    {
        $this->access = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role)
    {
        $role = strtoupper($role);

        if (!\in_array($role, $this->getAccess(), true)) {
            $this->access[] = $role;
        }

        return $this;
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
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    /**
     * @param Collection $articles
     */
    public function setArticles(Collection $articles): void
    {
        $this->articles = $articles;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isOwner(User $user): bool
    {
        return $this->getOwner()->getId() === $user->getId();
    }

    /**
     * @param $role
     * @return bool
     */
    public function hasAccess($role): bool
    {
        return \in_array(strtoupper($role), $this->getArrayAccess(), true);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
