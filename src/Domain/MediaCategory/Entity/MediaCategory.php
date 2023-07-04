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

namespace App\Domain\MediaCategory\Entity;

use App\Application\Constant\TranslationConstant;
use App\Application\Traits\Entity\{
    IdTrait,
    IsOwnerTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use App\Domain\Media\Entity\Media;
use App\Domain\MediaCategory\Repository\MediaCategoryRepository;
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'media_category')]
#[ORM\Entity(repositoryClass: MediaCategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class MediaCategory
{
    use IdTrait;
    use SimpleInformationTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    #[ORM\ManyToMany(targetEntity: Media::class, mappedBy: 'categories')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $medias;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'mediaCategoriesOwner')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private User $owner;

    public function __construct()
    {
        $this->medias = new ArrayCollection;
    }

    /**
     * @return Collection<Media>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function setMedias(Collection $medias): void
    {
        $this->medias = $medias;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY->value;
    }
}
