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

use DateTime;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use App\Constant\{
    MediaConstant,
    FileSizeConstant,
    TranslationConstant
};
use App\Entity\Traits\{
    IdTrait,
    IsOwnerTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Table(name="media")
 * @ORM\Entity(repositoryClass="App\Repository\MediaRepository")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="mediaOwner", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?User $owner = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaType", inversedBy="medias", fetch="EAGER")
     * @ORM\JoinColumn(name="media_type_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?MediaType $type = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaMimeType", inversedBy="medias", fetch="EAGER")
     * @ORM\JoinColumn(name="media_mime_type_id", referencedColumnName="id", nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?MediaMimeType $mimeType = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\MediaCategory", inversedBy="medias")
     * @ORM\JoinTable(name="media_to_media_category",
     *      joinColumns={@ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="media_category_id", referencedColumnName="id", nullable=true)
     * })
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $categories = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Work", inversedBy="medias", fetch="EAGER")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Work $work = null;

    /**
     * @ORM\Column(name="media_name", type="string", nullable=false, unique=true)
     * @Gedmo\Versioned
     */
    private ?string $mediaName = null;

    /**
     * @ORM\Column(name="original_name", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private ?string $originalMediaName = null;

    /**
     * @ORM\Column(name="original_extension", type="string", length=5, nullable=true)
     * @Gedmo\Versioned
     */
    private ?string $originalExtension = null;

    /**
     * @ORM\Column(name="media_size", type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private ?int $mediaSize = null;

    private ?UploadedFile $uploadMedia = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemEvent", mappedBy="media")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="default")
     */
    private ?Collection $systemEvents = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection;
        $this->systemEvents = new ArrayCollection;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    public function getMediaName(): ?string
    {
        return $this->mediaName;
    }

    public function getMediaNameFolderType(): string
    {
        return $this->getType()->getFolder() . DIRECTORY_SEPARATOR . $this->getMediaName();
    }

    public function setMediaName(string $mediaName): void
    {
        $this->mediaName = $mediaName;
    }

    public function getType(): ?MediaType
    {
        return $this->type;
    }

    public function setType(MediaType $type): void
    {
        $this->type = $type;
    }

    public function getMimeType(): ?MediaMimeType
    {
        return $this->mimeType;
    }

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

    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function setWork(?Work $work): void
    {
        $this->work = $work;
    }

    public function getOriginalMediaName(): ?string
    {
        return $this->originalMediaName;
    }

    public function setOriginalMediaName(string $originalMediaName): void
    {
        $this->originalMediaName = $originalMediaName;
    }

    public function getOriginalExtension(): ?string
    {
        return $this->originalExtension;
    }

    public function setOriginalExtension(string $originalExtension): void
    {
        $this->originalExtension = $originalExtension;
    }

    public function getMediaSize(): ?int
    {
        return $this->mediaSize;
    }

    public function setMediaSize(int $mediaSize): void
    {
        $this->mediaSize = $mediaSize;
    }

    public function setUploadMedia(?UploadedFile $uploadMedia): void
    {
        $this->uploadMedia = $uploadMedia;
        $this->setUpdatedAt(new DateTime);
    }

    public function getUploadMedia(): ?UploadedFile
    {
        return $this->uploadMedia;
    }

    public function getNameWithExtension(): string
    {
        return $this->getMediaName() . '.' . $this->getMimeType()->getExtension();
    }

    public function getUploadDir(): string
    {
        return MediaConstant::WEB_PATH_TO_UPLOAD_FOLDER . $this->getType()->getFolder();
    }

    public function getAbsolutePath(): string
    {
        return $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->getMediaName();
    }

    public function getAbsolutePathWithExtension(): string
    {
        return $this->getUploadRootDir() .
            DIRECTORY_SEPARATOR .
            $this->getMediaName() .
            $this->getMimeType()->getExtension();
    }

    public function getWebPath(): string
    {
        return $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->getMediaName();
    }

    public function getWebPathWithExtension(): string
    {
        return $this->getUploadDir() .
            DIRECTORY_SEPARATOR .
            $this->getMediaName() .
            '.' .
            $this->getMimeType()->getExtension();
    }

    private function getUploadRootDir(): string
    {
        return MediaConstant::SERVER_PATH_TO_PUBLIC_FOLDER . DIRECTORY_SEPARATOR . $this->getUploadDir();
    }

    public function getMediaSizeFormatted(): string
    {
        $size = $this->getMediaSize();
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        $number = number_format(
            $size / (1024 ** $power),
            2,
            '.',
            ','
        );

        return sprintf('%s %s', $number, FileSizeConstant::FILE_SIZES[$power]);
    }

    public function removeMediaFile(): bool
    {
        $pathToFile = $this->getAbsolutePath();
        if (file_exists($pathToFile)) {
            return unlink($pathToFile);
        }

        return false;
    }

    public function existMediaFile(): bool
    {
        return file_exists($this->getAbsolutePath());
    }

    public function getFolder(): ?string
    {
        return $this->getType()?->getFolder();
    }

    public function changeActive(): void
    {
        $this->setActive(!$this->isActive());
    }

    public function __toString(): string
    {
        return $this->getName() ?: TranslationConstant::EMPTY;
    }
}
