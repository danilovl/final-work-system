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

namespace App\Domain\MediaMimeType\Bus\Query\MediaMimeTypeListByOwner;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\MediaMimeType\Facade\MediaMimeTypeFacade;

readonly class GetMediaMimeTypeListByOwnerQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MediaMimeTypeFacade $mediaMimeTypeFacade) {}

    public function __invoke(GetMediaMimeTypeListByOwnerQuery $query): GetMediaMimeTypeListByOwnerQueryResult
    {
        $mediaMimeTypes = $this->mediaMimeTypeFacade->listByOwner(
            user: $query->user,
            mediaType: $query->mediaType,
            onlyKey: $query->onlyKey
        );

        return new GetMediaMimeTypeListByOwnerQueryResult($mediaMimeTypes);
    }
}
