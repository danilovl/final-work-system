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

use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Constant\TranslationConstant;
use App\Entity\Traits\{
    IdTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="media_mime_type")
 * @ORM\Entity(repositoryClass="App\Repository\MediaMimeTypeRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class MediaMimeType
{
    use IdTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;

    /**
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Gedmo\Versioned
     */
    private ?string $name = null;

    /**
     * @ORM\Column(name="extension", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private ?string $extension = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media", mappedBy="mimeType")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $medias = null;

    public function __construct()
    {
        $this->medias = new ArrayCollection;
    }

    public function getName(): ?string
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
