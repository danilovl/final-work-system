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

namespace App\Domain\Media\Bus\Query\MediaQueryByUserFilter;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Media\Facade\MediaFacade;

readonly class GetMediaQueryByUserFilterQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MediaFacade $mediaFacade) {}

    public function __invoke(GetMediaQueryByUserFilterQuery $query): GetMediaQueryByUserFilterQueryResult
    {
        $query = $this->mediaFacade->queryByUserFilter($query->mediaRepositoryDTO);

        return new GetMediaQueryByUserFilterQueryResult($query);
    }
}
