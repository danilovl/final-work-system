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

use Doctrine\ORM\EntityManager;
use FinalWork\FinalWorkBundle\Entity\MediaType;
use FinalWork\FinalWorkBundle\Entity\Repository\MediaTypeRepository;

class MediaTypeFacade
{
    /**
     * @var MediaTypeRepository
     */
    private $mediaTypeRepository;

    /**
     * MediaTypeFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->mediaTypeRepository = $entityManager->getRepository(MediaType::class);
    }

    /**
     * @param int $id
     * @return MediaType|null
     */
    public function find(int $id): ?MediaType
    {
        return $this->mediaTypeRepository->find($id);
    }
}
