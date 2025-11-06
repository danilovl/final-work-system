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

namespace App\Domain\DocumentCategory\Bus\Query\DocumentCategoryList;

use App\Domain\MediaCategory\Entity\MediaCategory;
use Knp\Component\Pager\Pagination\PaginationInterface;

readonly class GetDocumentCategoryListQueryResult
{
    /**
     * @param PaginationInterface<int, MediaCategory> $documents
     */
    public function __construct(public PaginationInterface $documents) {}
}
