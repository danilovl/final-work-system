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

namespace App\Domain\MediaMimeType\Bus\Query\MediaMimeTypeList;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\MediaMimeType\Facade\MediaMimeTypeFacade;

readonly class GetMediaMimeTypeListQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MediaMimeTypeFacade $mediaMimeTypeFacade) {}

    public function __invoke(GetMediaMimeTypeListQuery $query): GetMediaMimeTypeListQueryResult
    {
        $mediaMimeTypes = $this->mediaMimeTypeFacade->list($query->onlyKey);

        return new GetMediaMimeTypeListQueryResult($mediaMimeTypes);
    }
}
