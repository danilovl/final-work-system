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

namespace App\Domain\WorkCategory\Bus\Query\WorkCategories;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\WorkCategory\Facade\WorkCategoryFacade;

readonly class GetWorkCategoriesQueryHandler implements QueryHandlerInterface
{
    public function __construct(private WorkCategoryFacade $workCategoryFacade) {}

    public function __invoke(GetWorkCategoriesQuery $query): GetWorkCategoriesQueryResult
    {
        $workCategories = $this->workCategoryFacade->getByIds($query->ids);

        return new GetWorkCategoriesQueryResult($workCategories);
    }
}
