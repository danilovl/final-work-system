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

namespace App\Domain\ApiUser\Facade;

use App\Domain\ApiUser\Entity\ApiUser;
use App\Domain\ApiUser\Repository\ApiUserRepository;

readonly class ApiUserFacade
{
    public function __construct(private ApiUserRepository $apiUserRepository) {}

    public function findByApiKey(string $apiKey): ?ApiUser
    {
        /** @var ApiUser|null $result */
        $result = $this->apiUserRepository
            ->byApiKey($apiKey)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
