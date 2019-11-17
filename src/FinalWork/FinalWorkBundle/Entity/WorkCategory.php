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
    IsOwnerTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use FinalWork\SonataUserBundle\Entity\User;

/**
 * @ORM\Table(name="work_category")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\WorkCategoryRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
 * @Gedmo\Loggable
 */
class WorkCategory
{
    use IdTrait;
    use SimpleInformationTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="workCategoriesOwner")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
     */
    private $owner;

    /**
     * @var Collection|Work[]
     *
     * @ORM\ManyToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", mappedBy="categories")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
     */
    private $works;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sorting", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private $sorting;

    /**
     * WorkCategory constructor.
     */
    public function __construct()
    {
        $this->works = new ArrayCollection;
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
     * @return Collection|Work[]
     */
    public function getWorks(): Collection
    {
        return $this->works;
    }

    /**
     * @param Collection $works
     */
    public function setWorks(Collection $works): void
    {
        $this->works = $works;
    }

    /**
     * @return string|null
     */
    public function getSorting(): ?string
    {
        return $this->sorting;
    }

    /**
     * @param string|null $sorting
     */
    public function setSorting(?string $sorting): void
    {
        $this->sorting = $sorting;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
