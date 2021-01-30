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

namespace App\Model\ApiUser;

use App\Entity\ApiUser;
use App\Repository\ApiUserRepository;

class ApiUserFacade
{
    public function __construct(private ApiUserRepository $apiUserRepository)
    {
    }

    public function findByApiKey(string $apiKey): ?ApiUser
    {
        return $this->apiUserRepository
            ->byApiKey($apiKey)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
