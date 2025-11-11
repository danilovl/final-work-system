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

namespace App\Domain\WorkCategory\Bus\Query\WorkCategoryList;

use App\Domain\WorkCategory\Entity\WorkCategory;
use Knp\Component\Pager\Pagination\PaginationInterface;

readonly class GetWorkCategoryListQueryResult
{
    /**
     * @param PaginationInterface<int, WorkCategory> $workCategories
     */
    public function __construct(public PaginationInterface $workCategories) {}
}
