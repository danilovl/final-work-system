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

namespace App\Domain\Work\Bus\Query\ActiveAuthors;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Work\Service\UserWorkService;

readonly class GetActiveAuthorsQueryHandler implements QueryHandlerInterface
{
    public function __construct(private UserWorkService $userWorkService) {}

    public function __invoke(GetActiveAuthorsQuery $query): GetActiveAuthorsQueryResult
    {
        $authors = $this->userWorkService->getActiveAuthor(
            user: $query->user,
            userType: $query->userType
        );

        return new GetActiveAuthorsQueryResult($authors);
    }
}
