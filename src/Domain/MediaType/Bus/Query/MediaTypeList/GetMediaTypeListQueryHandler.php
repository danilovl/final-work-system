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

namespace App\Domain\MediaType\Bus\Query\MediaTypeList;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\MediaType\Facade\MediaTypeFacade;

readonly class GetMediaTypeListQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MediaTypeFacade $mediaTypeFacade) {}

    public function __invoke(GetMediaTypeListQuery $query): GetMediaTypeListQueryResult
    {
        $mediaTypes = $this->mediaTypeFacade->list();

        return new GetMediaTypeListQueryResult($mediaTypes);
    }
}
