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

use DateTime;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Exception;
use FinalWork\FinalWorkBundle\Constant\{
    MediaConstant,
    FileSizeConstant,
    TranslationConstant
};
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Table(name="media")
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\MediaRepository")
 * @ORM\EntityListeners({"FinalWork\FinalWorkBundle\Entity\Listener\MediaUploadListener"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
 * @Gedmo\Loggable
 */
class Media
{
    use IdTrait;
    use SimpleInformationTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\SonataUserBundle\Entity\User", inversedBy="mediaOwner", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $owner;

    /**
     * @var MediaType|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\MediaType", inversedBy="medias", fetch="EAGER")
     * @ORM\JoinColumn(name="media_type_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $type;

    /**
     * @var MediaMimeType|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\MediaMimeType", inversedBy="medias", fetch="EAGER")
     * @ORM\JoinColumn(name="media_mime_type_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $mimeType;

    /**
     * @var Collection|MediaCategory[]
     *
     * @ORM\ManyToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\MediaCategory", inversedBy="medias")
     * @ORM\JoinTable(name="media_to_media_category",
     *      joinColumns={@ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="media_category_id", referencedColumnName="id", nullable=true)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $categories;

    /**
     * @var Work|null
     *
     * @ORM\ManyToOne(targetEntity="FinalWork\FinalWorkBundle\Entity\Work", inversedBy="medias", fetch="EAGER")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $work;

    /**
     * @var string
     *
     * @ORM\Column(name="media_name", type="string", nullable=false, unique=true)
     * @Gedmo\Versioned
     */
    private $mediaName;

    /**
     * @var string
     *
     * @ORM\Column(name="original_name", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private $originalMediaName;

    /**
     * @var string
     *
     * @ORM\Column(name="original_extension", type="string", length=5, nullable=true)
     * @Gedmo\Versioned
     */
    private $originalExtension;

    /**
     * @var integer
     *
     * @ORM\Column(name="media_size", type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $mediaSize;

    /**
     * @var UploadedFile
     */
    private $uploadMedia;

    /**
     * @var Collection|SystemEvent[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\SystemEvent", mappedBy="media")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private $systemEvents;

    /**
     * Media constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection;
        $this->systemEvents = new ArrayCollection;
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
     * @return string|null
     */
    public function getMediaName(): ?string
    {
        return $this->mediaName;
    }

    /**
     * @return string
     */
    public function getMediaNameFolderType(): string
    {
        return $this->getType()->getFolder() . DIRECTORY_SEPARATOR . $this->getMediaName();
    }

    /**
     * @param string $mediaName
     */
    public function setMediaName(string $mediaName): void
    {
        $this->mediaName = $mediaName;
    }

    /**
     * @return MediaType|null
     */
    public function getType(): ?MediaType
    {
        return $this->type;
    }

    /**
     * @param MediaType $type
     */
    public function setType(MediaType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return MediaMimeType|null
     */
    public function getMimeType(): ?MediaMimeType
    {
        return $this->mimeType;
    }

    /**
     * @param MediaMimeType $mimeType
     */
    public function setMimeType(MediaMimeType $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return Collection|MediaCategory[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param Collection $categories
     */
    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return Work|null
     */
    public function getWork(): ?Work
    {
        return $this->work;
    }

    /**
     * @param Work $work
     */
    public function setWork(?Work $work): void
    {
        $this->work = $work;
    }

    /**
     * @return string|null
     */
    public function getOriginalMediaName(): ?string
    {
        return $this->originalMediaName;
    }

    /**
     * @param string $originalMediaName
     */
    public function setOriginalMediaName(string $originalMediaName): void
    {
        $this->originalMediaName = $originalMediaName;
    }

    /**
     * @return string|null
     */
    public function getOriginalExtension(): ?string
    {
        return $this->originalExtension;
    }

    /**
     * @param string $originalExtension
     */
    public function setOriginalExtension(string $originalExtension): void
    {
        $this->originalExtension = $originalExtension;
    }

    /**
     * @return int|null
     */
    public function getMediaSize(): ?int
    {
        return $this->mediaSize;
    }

    /**
     * @param int $mediaSize
     */
    public function setMediaSize(int $mediaSize): void
    {
        $this->mediaSize = $mediaSize;
    }

    /**
     * @param UploadedFile $uploadMedia
     * @throws Exception
     */
    public function setUploadMedia(UploadedFile $uploadMedia = null): void
    {
        $this->uploadMedia = $uploadMedia;
        $this->setUpdatedAt(new DateTime);
    }

    /**
     * @return  UploadedFile|null
     */
    public function getUploadMedia(): ?UploadedFile
    {
        return $this->uploadMedia;
    }

    /**
     * @return string|null
     */
    public function getNameWithExtension(): string
    {
        return $this->getMediaName() . '.' . $this->getMimeType()->getExtension();
    }

    /**
     * @return string
     */
    public function getUploadDir(): string
    {
        return MediaConstant::WEB_PATH_TO_UPLOAD_FOLDER . $this->getType()->getFolder();
    }

    /**
     * @return string
     */
    public function getAbsolutePath(): string
    {
        return $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->getMediaName();
    }

    /**
     * @return string
     */
    public function getAbsolutePathWithExtension(): string
    {
        return $this->getUploadRootDir() .
            DIRECTORY_SEPARATOR .
            $this->getMediaName() .
            $this->getMimeType()->getExtension();
    }

    /**
     * @return string
     */
    public function getWebPath(): string
    {
        return $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->getMediaName();
    }

    /**
     * @return string
     */
    public function getWebPathWithExtension(): string
    {
        return $this->getUploadDir() .
            DIRECTORY_SEPARATOR .
            $this->getMediaName() .
            '.' .
            $this->getMimeType()->getExtension();
    }

    /**
     * @return string|null
     */
    private function getUploadRootDir(): string
    {
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    /**
     * @return string
     */
    public function getMediaSizeFormatted(): string
    {
        $size = $this->getMediaSize();
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return number_format(
                $size / (1024 ** $power),
                2,
                '.',
                ','
            ) .
            ' ' .
            FileSizeConstant::FILE_SIZES[$power];
    }

    /**
     * @return bool|null
     */
    public function removeMediaFile(): bool
    {
        $pathToFile = $this->getAbsolutePath();
        if (file_exists($pathToFile)) {
            return unlink($pathToFile);
        }

        return false;
    }

    /**
     * @return bool|null
     */
    public function existMediaFile(): bool
    {
        $pathToFile = $this->getAbsolutePath();
        if (file_exists($pathToFile)) {
            return true;
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function getFolder(): ?string
    {
        if ($this->getType() === null) {
            return null;
        }

        return $this->getType()->getFolder();
    }

    /**
     * @return void
     */
    public function changeActive(): void
    {
        if ($this->isActive()) {
            $this->setActive(false);
        } else {
            $this->setActive(true);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
