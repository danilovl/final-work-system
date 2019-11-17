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

namespace FinalWork\FinalWorkBundle\Entity\Listener;

use FinalWork\FinalWorkBundle\Exception\RuntimeException;
use Doctrine\ORM\Event\{
    LifecycleEventArgs,
    PreUpdateEventArgs
};
use Exception;
use FinalWork\FinalWorkBundle\Entity\{
    Media,
    MediaMimeType
};
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaUploadListener
{
    /**
     * @param Media $media
     * @param LifecycleEventArgs $eventArgs
     * @throws Exception
     */
    public function prePersist(Media $media, LifecycleEventArgs $eventArgs): void
    {
        $this->create($media, $eventArgs);
    }

    /**
     * @param Media $media
     * @param PreUpdateEventArgs $eventArgs
     * @throws Exception
     */
    public function preUpdate(Media $media, PreUpdateEventArgs $eventArgs): void
    {
        $this->update($media, $eventArgs);
    }

    /**
     * @param Media $media
     * @throws Exception
     */
    public function preRemove(Media $media): void
    {
        $this->remove($media);
    }

    /**
     * @param Media $media
     * @param LifecycleEventArgs $eventArgs
     * @throws Exception
     */
    public function create(Media $media, LifecycleEventArgs $eventArgs): void
    {
        /** @var UploadedFile $uploadMedia */
        $uploadMedia = $media->getUploadMedia();
        $originalMediaName = $uploadMedia->getClientOriginalName();
        $originalMediaExtension = $uploadMedia->getClientOriginalExtension();
        $mimeType = $uploadMedia->getMimeType();
        $mediaSize = $uploadMedia->getSize();

        $em = $eventArgs->getEntityManager();
        $mediaMimeType = $em->getRepository(MediaMimeType::class)->findOneBy(['name' => $mimeType]);
        if ($mediaMimeType === null || empty($mediaMimeType)) {
            throw new RuntimeException("MediaMimeType doesn't exist");
        }

        $mediaName = sha1(uniqid((string)mt_rand(), true)) . '.' . $mediaMimeType->getExtension();
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

    /**
     * @param Media $media
     * @param PreUpdateEventArgs $eventArgs
     * @throws Exception
     */
    public function update(Media $media, PreUpdateEventArgs $eventArgs): void
    {
        $uploadMedia = $media->getUploadMedia();
        if ($uploadMedia) {
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

            $mediaName = sha1(uniqid((string)mt_rand(), true)) . '.' . $mediaMimeType->getExtension();
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

    /**
     * @param Media $media
     * @throws Exception
     */
    public function remove(Media $media): void
    {
        $media->removeMediaFile();
    }
}