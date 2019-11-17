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

use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use FinalWork\SonataUserBundle\Entity\User;

/**
 * @ORM\Table(name="media_category")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\MediaCategoryRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
 * @Gedmo\Loggable
 */
class MediaCategory
{
    use IdTrait;
    use SimpleInformationTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    /**
     * @var Collection|Media[]
     *
     * @ORM\ManyToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Media", mappedBy="categories")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $medias;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="mediaCategoriesOwner")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * MediaCategory constructor.
     */
    public function __construct()
    {
        $this->medias = new ArrayCollection;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    /**
     * @param Collection $medias
     */
    public function setMedias(Collection $medias): void
    {
        $this->medias = $medias;
    }

    /**
     * @return User|null
     */
    public function getOwner(): User
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
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
