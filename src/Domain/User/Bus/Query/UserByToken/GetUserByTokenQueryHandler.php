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

namespace App\Domain\User\Bus\Query\UserByToken;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\User\Facade\UserFacade;

readonly class GetUserByTokenQueryHandler implements QueryHandlerInterface
{
    public function __construct(private UserFacade $userFacade) {}

    public function __invoke(GetUserByTokenQuery $query): GetUserByTokenQueryResult
    {
        $user = $this->userFacade->findByToken(
            username: $query->username,
            token: $query->token
        );

        return new GetUserByTokenQueryResult($user);
    }
}
