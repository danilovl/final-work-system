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

namespace App\Domain\Media\EventListener\Entity;

use App\Application\Exception\RuntimeException;
use App\Application\Service\{
    S3ClientService,
    EntityManagerService
};
use App\Domain\Media\Entity\Media;
use App\Domain\MediaMimeType\Entity\MediaMimeType;
use Doctrine\ORM\Event\{
    PrePersistEventArgs,
    PreRemoveEventArgs,
    PreUpdateEventArgs
};

readonly class MediaUploadListener
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private S3ClientService $s3ClientService
    ) {}

    public function prePersist(PrePersistEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();
        if (!$entity instanceof Media) {
            return;
        }

        $this->create($entity);
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();
        if (!$entity instanceof Media) {
            return;
        }

        $this->update($entity);
    }

    public function preRemove(PreRemoveEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();
        if (!$entity instanceof Media) {
            return;
        }

        $this->remove($entity);
    }

    private function create(Media $media): void
    {
        $uploadMedia = $media->getUploadMediaMust();
        $media->setUploadMedia(null);
        $originalMediaName = $uploadMedia->getClientOriginalName();
        $originalMediaExtension = $uploadMedia->getClientOriginalExtension();
        $mimeType = $uploadMedia->getMimeType();
        $mediaSize = $uploadMedia->getSize();

        /** @var MediaMimeType|null $mediaMimeType */
        $mediaMimeType = $this->entityManagerService
            ->getRepository(MediaMimeType::class)
            ->findOneBy(['name' => $mimeType]);

        if ($mediaMimeType === null) {
            throw new RuntimeException("MediaMimeType doesn't exist");
        }

        $mediaName = sha1(uniqid((string) mt_rand(), true)) . '.' . $mediaMimeType->getExtension();

        $media->setName($media->getName());
        $media->setMediaName($mediaName);
        $media->setMimeType($mediaMimeType);
        $media->setOriginalMediaName($originalMediaName);
        $media->setOriginalExtension($originalMediaExtension);
        $media->setMediaSize($mediaSize);

        $this->s3ClientService->putObject(
            $media->getType()->getFolder(),
            $media->getMediaName(),
            $uploadMedia->getRealPath()
        );
    }

    private function update(Media $media): void
    {
        $uploadMedia = $media->getUploadMedia();
        if (!$uploadMedia) {
            return;
        }

        $media->setUploadMedia(null);

        $originalMediaName = $uploadMedia->getClientOriginalName();
        $originalMediaExtension = $uploadMedia->getClientOriginalExtension();
        $mimeType = $uploadMedia->getMimeType();
        $mediaSize = $uploadMedia->getSize();

        /** @var MediaMimeType|null $mediaMimeType */
        $mediaMimeType = $this->entityManagerService
            ->getRepository(MediaMimeType::class)
            ->findOneBy(['name' => $mimeType]);

        if ($mediaMimeType === null) {
            throw new RuntimeException("MediaMimeType doesn't exist");
        }

        $this->remove($media);

        $mediaName = sha1(uniqid((string) mt_rand(), true)) . '.' . $mediaMimeType->getExtension();
        $media->setMediaName($mediaName);
        $media->setMimeType($mediaMimeType);

        $media->setOriginalMediaName($originalMediaName);
        $media->setOriginalExtension($originalMediaExtension);
        $media->setMediaSize($mediaSize);

        $this->s3ClientService->putObject(
            $media->getType()->getFolder(),
            $media->getMediaName(),
            $uploadMedia->getRealPath()
        );
    }

    private function remove(Media $media): void
    {
        $this->s3ClientService->deleteObject(
            $media->getType()->getFolder(),
            $media->getMediaName()
        );
    }
}
