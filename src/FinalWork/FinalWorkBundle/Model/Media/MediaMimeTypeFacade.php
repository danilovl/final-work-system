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

namespace FinalWork\FinalWorkBundle\Model\Media;

use Doctrine\ORM\{
    EntityManager,
    NonUniqueResultException
};
use FinalWork\FinalWorkBundle\Entity\MediaMimeType;
use FinalWork\FinalWorkBundle\Entity\Repository\MediaMimeTypeRepository;

class MediaMimeTypeFacade
{
    /**
     * @var MediaMimeTypeRepository
     */
    private $mediaMimeTypeRepository;

    /**
     * MediaMimeTypeService constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->mediaMimeTypeRepository = $entityManager->getRepository(MediaMimeType::class);
    }

    /**
     * @param bool $onlyKey
     * @return array
     */
    public function getFormValidationMimeTypes(bool $onlyKey = false): array
    {
        $mimeTypes = $this->mediaMimeTypeRepository
            ->getFormValidationMimeTypeName()
            ->getQuery()
            ->getResult();

        if ($onlyKey === true) {
            return array_keys($mimeTypes);
        }

        return $mimeTypes;
    }

    /**
     * @param $user
     * @param bool|array $mediaType
     * @param bool $onlyKey
     * @return array
     */
    public function getMimeTypesByOwner(
        $user,
        $mediaType = false,
        bool $onlyKey = false
    ): array {
        $mimeTypes = $this->mediaMimeTypeRepository
            ->findAllBy($user, $mediaType)
            ->getQuery()
            ->getResult();

        if ($onlyKey) {
            return array_keys($mimeTypes);
        }

        return $mimeTypes;
    }

    /**
     * @param string $name
     * @return MediaMimeType|null
     * @throws NonUniqueResultException
     */
    public function getMimeTypeByName(string $name): ?MediaMimeType
    {
        return $this->mediaMimeTypeRepository
            ->findByName($name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
