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

namespace App\Domain\ApiUser\Bus\Query\ApiUserByKey;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ApiUser\Facade\ApiUserFacade;

readonly class GetApiUserByKeyQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ApiUserFacade $apiUserFacade) {}

    public function __invoke(GetApiUserByKeyQuery $query): GetApiUserByKeyQueryResult
    {
        $apiUser = $this->apiUserFacade->findByApiKey($query->apiKey);

        return new GetApiUserByKeyQueryResult($apiUser);
    }
}
