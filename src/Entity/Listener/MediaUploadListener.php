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

namespace App\Entity\Listener;

use App\Constant\MediaTypeConstant;
use App\Exception\RuntimeException;
use Doctrine\ORM\Event\{
    LifecycleEventArgs,
    PreUpdateEventArgs
};
use App\Entity\Media;
use App\Entity\MediaType;
use App\Entity\MediaMimeType;

class MediaUploadListener
{
    private const DEFAULT_NAME = 'default media name';

    public function prePersist(Media $media, LifecycleEventArgs $eventArgs): void
    {
        $this->create($media, $eventArgs);
    }

    public function preUpdate(Media $media, PreUpdateEventArgs $eventArgs): void
    {
        $this->update($media, $eventArgs);
    }

    public function preRemove(Media $media): void
    {
        $this->remove($media);
    }

    public function create(Media $media, LifecycleEventArgs $eventArgs): void
    {
        $uploadMedia = $media->getUploadMedia();
        $media->setUploadMedia(null);
        $originalMediaName = $uploadMedia->getClientOriginalName();
        $originalMediaExtension = $uploadMedia->getClientOriginalExtension();
        $mimeType = $uploadMedia->getMimeType();
        $mediaSize = $uploadMedia->getSize();

        $em = $eventArgs->getEntityManager();
        $mediaMimeType = $em->getRepository(MediaMimeType::class)->findOneBy(['name' => $mimeType]);
        if ($mediaMimeType === null || empty($mediaMimeType)) {
            throw new RuntimeException("MediaMimeType doesn't exist");
        }

        $mediaName = sha1(uniqid((string) mt_rand(), true)) . '.' . $mediaMimeType->getExtension();

        $media->setName($media->getName() ?? self::DEFAULT_NAME);
        $media->setType($em->getReference(MediaType::class, MediaTypeConstant::USER_PROFILE_IMAGE));
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

    public function update(Media $media, PreUpdateEventArgs $eventArgs): void
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

    public function remove(Media $media): void
    {
        $media->removeMediaFile();
    }
}