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

namespace App\Domain\Media\Entity;

use App\Application\Exception\PropertyValueIsNullException;
use App\Application\Constant\FileSizeConstant;
use App\Domain\User\Traits\Entity\IsOwnerTrait;
use App\Application\Traits\Entity\{
    IdTrait,
    ActiveAbleTrait,
    CreateUpdateAbleTrait,
    SimpleInformationTrait
};
use App\Domain\Media\Repository\MediaRepository;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaMimeType\Entity\MediaMimeType;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use DateTime;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Table(name: 'media')]
#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
#[Gedmo\Loggable]
class Media
{
    use IdTrait;
    use SimpleInformationTrait;
    use ActiveAbleTrait;
    use CreateUpdateAbleTrait;
    use IsOwnerTrait;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], fetch: 'EAGER', inversedBy: 'mediaOwner')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private User $owner;

    #[ORM\ManyToOne(targetEntity: MediaType::class, fetch: 'EAGER', inversedBy: 'medias')]
    #[ORM\JoinColumn(name: 'media_type_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private MediaType $type;

    #[ORM\ManyToOne(targetEntity: MediaMimeType::class, fetch: 'EAGER', inversedBy: 'medias')]
    #[ORM\JoinColumn(name: 'media_mime_type_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private MediaMimeType $mimeType;

    /** @var Collection<MediaCategory> */
    #[ORM\ManyToMany(targetEntity: MediaCategory::class, inversedBy: 'medias')]
    #[ORM\JoinTable(name: 'media_to_media_category')]
    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id', nullable: true, onDelete: 'RESTRICT')]
    #[ORM\InverseJoinColumn(name: 'media_category_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $categories;

    #[ORM\ManyToOne(targetEntity: Work::class, fetch: 'EAGER', inversedBy: 'medias')]
    #[ORM\JoinColumn(name: 'work_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private ?Work $work = null;

    #[ORM\Column(name: 'media_name', type: Types::STRING, unique: true, nullable: false)]
    #[Gedmo\Versioned]
    private string $mediaName;

    #[ORM\Column(name: 'original_name', type: Types::STRING, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $originalMediaName = null;

    #[ORM\Column(name: 'original_extension', type: Types::STRING, length: 5, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $originalExtension = null;

    #[ORM\Column(name: 'media_size', type: Types::INTEGER, nullable: true)]
    #[Gedmo\Versioned]
    private ?int $mediaSize = null;

    private ?UploadedFile $uploadMedia = null;

    /** @var Collection<SystemEvent> */
    #[ORM\OneToMany(targetEntity: SystemEvent::class, mappedBy: 'media')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'default')]
    private Collection $systemEvents;

    public function __construct()
    {
        $this->categories = new ArrayCollection;
        $this->systemEvents = new ArrayCollection;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    public function getMediaName(): string
    {
        return $this->mediaName;
    }

    public function setMediaName(string $mediaName): void
    {
        $this->mediaName = $mediaName;
    }

    public function getType(): MediaType
    {
        return $this->type;
    }

    public function setType(MediaType $type): void
    {
        $this->type = $type;
    }

    public function getMimeType(): MediaMimeType
    {
        return $this->mimeType;
    }

    public function setMimeType(MediaMimeType $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return Collection<MediaCategory>
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

    #[Ignore]
    public function getWorkMust(): Work
    {
        if (!$this->work) {
            throw new PropertyValueIsNullException('Work is null.');
        }

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

    public function setOriginalMediaName(?string $originalMediaName): void
    {
        $this->originalMediaName = $originalMediaName;
    }

    public function getOriginalExtension(): ?string
    {
        return $this->originalExtension;
    }

    public function setOriginalExtension(?string $originalExtension): void
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

    #[Ignore]
    public function getUploadMediaMust(): UploadedFile
    {
        if (!$this->uploadMedia) {
            throw new PropertyValueIsNullException('UploadMedia is null.');
        }

        return $this->uploadMedia;
    }

    /**
     * @note use in template
     */
    #[Ignore]
    public function getMediaSizeFormatted(): string
    {
        $size = $this->getMediaSize();
        $power = $size > 0 ? floor(log($size, 1_024)) : 0;

        $number = number_format($size / (1_024 ** $power), 2);

        return sprintf('%s %s', $number, FileSizeConstant::FILE_SIZES[$power]);
    }

    public function changeActive(): void
    {
        $this->setActive(!$this->isActive());
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
