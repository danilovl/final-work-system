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

namespace App\Domain\MediaCategory\Bus\Query\MediaCategoryListByOwners;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\MediaCategory\Facade\MediaCategoryFacade;

readonly class GetMediaCategoryListByOwnersQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MediaCategoryFacade $mediaCategoryFacade) {}

    public function __invoke(GetMediaCategoryListByOwnersQuery $query): GetMediaCategoryListByOwnersQueryResult
    {
        $mediaCategories = $this->mediaCategoryFacade->listByOwners($query->users);

        return new GetMediaCategoryListByOwnersQueryResult($mediaCategories);
    }
}
