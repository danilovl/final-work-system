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

namespace App\Domain\User\Bus\Query\UserSearchQueryBuilder;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\User\Facade\UserFacade;

readonly class GetUserSearchQueryBuilderQueryHandler implements QueryHandlerInterface
{
    public function __construct(private UserFacade $userFacade) {}

    public function __invoke(GetUserSearchQueryBuilderQuery $query): GetUserSearchQueryBuilderQueryResult
    {
        $queryBuilder = $this->userFacade->queryBuilderBySearchType(
            user: $query->user,
            lookingUsers: $query->lookingUsers,
            whoLooking: $query->whoLooking
        );

        return new GetUserSearchQueryBuilderQueryResult($queryBuilder);
    }
}
