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

namespace App\Domain\Work\Bus\Query\UserWorks;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Work\Service\UserWorkService;

readonly class GetUserWorksQueryHandler implements QueryHandlerInterface
{
    public function __construct(private UserWorkService $userWorkService) {}

    public function __invoke(GetUserWorksQuery $query): GetUserWorksQueryResult
    {
        $works = $this->userWorkService->getWorkBy(
            user: $query->user,
            userType: $query->userType,
            type: $query->type,
            status: $query->status
        );

        return new GetUserWorksQueryResult($works);
    }
}
