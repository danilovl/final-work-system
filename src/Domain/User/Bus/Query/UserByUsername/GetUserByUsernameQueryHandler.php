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

namespace App\Domain\User\Bus\Query\UserByUsername;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\User\Facade\UserFacade;

readonly class GetUserByUsernameQueryHandler implements QueryHandlerInterface
{
    public function __construct(private UserFacade $userFacade) {}

    public function __invoke(GetUserByUsernameQuery $query): GetUserByUsernameQueryResult
    {
        $user = $this->userFacade->findByUsername($query->username);

        return new GetUserByUsernameQueryResult($user);
    }
}
