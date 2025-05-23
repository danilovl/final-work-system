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

namespace App\Domain\Document\Bus\Query\DocumentList;

use App\Domain\Media\Entity\Media;
use Knp\Component\Pager\Pagination\PaginationInterface;

readonly class GetDocumentListQueryResult
{
    /**
     * @param PaginationInterface<int, Media> $documents
     */
    public function __construct(public PaginationInterface $documents) {}
}
