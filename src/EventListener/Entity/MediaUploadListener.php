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

namespace App\EventListener\Entity;

use App\Exception\RuntimeException;
use App\Model\Media\Entity\Media;
use App\Model\MediaMimeType\Entity\MediaMimeType;
use Doctrine\ORM\Event\{
    LifecycleEventArgs,
    PreUpdateEventArgs
};

class MediaUploadListener
{
    private const DEFAULT_NAME = 'default media name';

    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Media) {
            return;
        }

        $this->create($entity, $eventArgs);
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Media) {
            return;
        }

        $this->update($entity, $eventArgs);
    }

    public function preRemove(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Media) {
            return;
        }

        $this->remove($entity);
    }

    private function create(Media $media, LifecycleEventArgs $eventArgs): void
    {
        $uploadMedia = $media->getUploadMedia();
        $media->setUploadMedia(null);
        $originalMediaName = $uploadMedia->getClientOriginalName();
        $originalMediaExtension = $uploadMedia->getClientOriginalExtension();
        $mimeType = $uploadMedia->getMimeType();
        $mediaSize = $uploadMedia->getSize();

        /** @var MediaMimeType|null $mediaMimeType */
        $mediaMimeType = $eventArgs->getEntityManager()
            ->getRepository(MediaMimeType::class)
            ->findOneBy(['name' => $mimeType]);

        if ($mediaMimeType === null) {
            throw new RuntimeException("MediaMimeType doesn't exist");
        }

        $mediaName = sha1(uniqid((string) mt_rand(), true)) . '.' . $mediaMimeType->getExtension();

        $media->setName($media->getName() ?? self::DEFAULT_NAME);
        $media->setMediaName($mediaName);
        $media->setMimeType($mediaMimeType);
        $media->setOriginalMediaName($originalMediaName);
        $media->setOriginalExtension($originalMediaExtension);
        $media->setMediaSize($mediaSize);

        $uploadMedia->move(
            $media->getUploadDir(),
            $mediaName
        );
    }

    private function update(Media $media, PreUpdateEventArgs $eventArgs): void
    {
        $uploadMedia = $media->getUploadMedia();
        if ($uploadMedia) {
            $media->setUploadMedia(null);
            $em = $eventArgs->getEntityManager();

            $originalMediaName = $uploadMedia->getClientOriginalName();
            $originalMediaExtension = $uploadMedia->getClientOriginalExtension();
            $mimeType = $uploadMedia->getMimeType();
            $mediaSize = $uploadMedia->getSize();

            $mediaMimeType = $em->getRepository(MediaMimeType::class)->findOneBy(['name' => $mimeType]);
            if ($mediaMimeType === null || empty($mediaMimeType)) {
                throw new RuntimeException("MediaMimeType doesn't exist");
            }

            $media->removeMediaFile();

            $mediaName = sha1(uniqid((string) mt_rand(), true)) . '.' . $mediaMimeType->getExtension();
            $media->setMediaName($mediaName);
            $media->setMimeType($mediaMimeType);

            $media->setOriginalMediaName($originalMediaName);
            $media->setOriginalExtension($originalMediaExtension);
            $media->setMediaSize($mediaSize);

            $uploadMedia->move(
                $media->getUploadDir(),
                $mediaName
            );
        }
    }

    private function remove(Media $media): void
    {
        $media->removeMediaFile();
    }
}