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

use App\Repository\MediaTypeRepository;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    ConstantAwareTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @Gedmo\Loggable
 */
#[ORM\Table(name: 'media_type')]
#[ORM\Entity(repositoryClass: MediaTypeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
class MediaType
{
    use IdTrait;
    use SimpleInformationTrait;
    use ConstantAwareTrait;
    use CreateUpdateAbleTrait;

    /**
     * @Gedmo\Versioned
     */
    #[ORM\Column(name: 'folder', type: Types::STRING, nullable: false)]
    private ?string $folder = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Media::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Collection $medias = null;

    public function __construct()
    {
        $this->medias = new ArrayCollection;
    }

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): void
    {
        $this->folder = $folder;
    }

    /**
     * @return Collection|Media[]
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
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}

