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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait
};

/**
 * @ORM\Table(name="media_mime_type")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\MediaMimeTypeRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
 * @Gedmo\Loggable
 */
class MediaMimeType
{
    use IdTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="extension", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private $extension;

    /**
     * @var Collection|Media[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Media", mappedBy="mimeType")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $medias;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
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

    /**
     * @param Collection $medias
     */
    public function setMedias(Collection $medias): void
    {
        $this->medias = $medias;
    }

    /**
     * @return null|string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
