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

namespace App\Domain\Work\Bus\Query\WorkList;

use App\Domain\Work\Entity\Work;
use Knp\Component\Pager\Pagination\PaginationInterface;

readonly class GetWorkListQueryResult
{
    /**
     * @param PaginationInterface<string, array{works?: Work[]}> $workGroups
     */
    public function __construct(public PaginationInterface $workGroups) {}
}
