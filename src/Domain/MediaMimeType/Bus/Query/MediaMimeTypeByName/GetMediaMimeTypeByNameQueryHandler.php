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

namespace App\Domain\MediaMimeType\Bus\Query\MediaMimeTypeByName;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\MediaMimeType\Facade\MediaMimeTypeFacade;

readonly class GetMediaMimeTypeByNameQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MediaMimeTypeFacade $mediaMimeTypeFacade) {}

    public function __invoke(GetMediaMimeTypeByNameQuery $query): GetMediaMimeTypeByNameQueryResult
    {
        $mediaMimeType = $this->mediaMimeTypeFacade->findByName($query->name);

        return new GetMediaMimeTypeByNameQueryResult($mediaMimeType);
    }
}
