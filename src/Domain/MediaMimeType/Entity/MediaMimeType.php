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

namespace App\Domain\MediaMimeType\Entity;

use App\Application\Traits\Entity\{
    IdTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait
};
use App\Domain\Media\Entity\Media;
use App\Domain\MediaMimeType\Repository\MediaMimeTypeRepository;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'media_mime_type')]
#[ORM\Entity(repositoryClass: MediaMimeTypeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class MediaMimeType
{
    use IdTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;

    #[ORM\Column(name: 'name', type: Types::STRING, nullable: false)]
    #[Gedmo\Versioned]
    private string $name;

    #[ORM\Column(name: 'extension', type: Types::STRING, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $extension = null;

    /** @var Collection<Media> */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'mimeType')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $medias;

    public function __construct()
    {
        $this->medias = new ArrayCollection;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
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
        return $this->name;
    }
}
