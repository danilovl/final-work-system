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

namespace App\Domain\MediaCategory\Bus\Query\MediaCategoryQueryByOwner;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\MediaCategory\Facade\MediaCategoryFacade;

readonly class GetMediaCategoryQueryByOwnerQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MediaCategoryFacade $mediaCategoryFacade) {}

    public function __invoke(GetMediaCategoryQueryByOwnerQuery $query): GetMediaCategoryQueryByOwnerQueryResult
    {
        $query = $this->mediaCategoryFacade->queryByOwner($query->user);

        return new GetMediaCategoryQueryByOwnerQueryResult($query);
    }
}
