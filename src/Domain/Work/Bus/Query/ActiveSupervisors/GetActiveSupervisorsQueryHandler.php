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

namespace App\Domain\Work\Bus\Query\ActiveSupervisors;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Work\Service\UserWorkService;

readonly class GetActiveSupervisorsQueryHandler implements QueryHandlerInterface
{
    public function __construct(private UserWorkService $userWorkService) {}

    public function __invoke(GetActiveSupervisorsQuery $query): GetActiveSupervisorsQueryResult
    {
        $supervisors = $this->userWorkService->listUserActiveSupervisors($query->user);

        return new GetActiveSupervisorsQueryResult($supervisors);
    }
}
