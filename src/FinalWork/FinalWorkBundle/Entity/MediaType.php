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
    ConstantAwareTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};

/**
 * @ORM\Table(name="media_type")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\MediaTypeRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="cache_long_time")
 * @Gedmo\Loggable
 */
class MediaType
{
    use IdTrait;
    use SimpleInformationTrait;
    use ConstantAwareTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(name="folder", type="string", nullable=false)
     * @Gedmo\Versioned
     */
    private $folder;

    /**
     * @var Collection|Media[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\Media", mappedBy="type")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $medias;

    /**
     * @return string|null
     */
    public function getFolder(): ?string
    {
        return $this->folder;
    }

    /**
     * @param string $folder
     */
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

    /**
     * @param Collection $medias
     */
    public function setMedias(Collection $medias): void
    {
        $this->medias = $medias;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}

