<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\MediaType\Entity;

use App\Application\Traits\Entity\{
    IdTrait,
    ConstantAwareTrait,
    SimpleInformationTrait,
    CreateUpdateAbleTrait
};
use App\Domain\Media\Entity\Media;
use App\Domain\MediaType\Repository\MediaTypeRepository;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'media_type')]
#[ORM\Entity(repositoryClass: MediaTypeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class MediaType
{
    use IdTrait;
    use SimpleInformationTrait;
    use ConstantAwareTrait;
    use CreateUpdateAbleTrait;

    #[ORM\Column(name: 'folder', type: Types::STRING, nullable: false)]
    #[Gedmo\Versioned]
    private string $folder;

    /** @var Collection<Media> */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'type')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $medias;

    public function __construct()
    {
        $this->medias = new ArrayCollection;
    }

    public function getFolder(): string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): void
    {
        $this->folder = $folder;
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

    public function __toString(): string
    {
        return $this->getName();
    }
}
