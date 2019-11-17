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

namespace FinalWork\FinalWorkBundle\Model\ApiUser;

use Doctrine\ORM\NonUniqueResultException;
use FinalWork\FinalWorkBundle\Entity\ApiUser;
use FinalWork\FinalWorkBundle\Entity\Repository\ApiUserRepository;
use Doctrine\ORM\EntityManager;

class ApiUserFacade
{
    /**
     * @var ApiUserRepository
     */
    private $apiUserRepository;

    /**
     * ApiUserFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->apiUserRepository = $entityManager->getRepository(ApiUser::class);
    }

    /**
     * @param string $apiKey
     * @return ApiUser|null
     * @throws NonUniqueResultException
     */
    public function findByApiKey(string $apiKey): ?ApiUser
    {
        return $this->apiUserRepository
            ->findByApiKey($apiKey)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
