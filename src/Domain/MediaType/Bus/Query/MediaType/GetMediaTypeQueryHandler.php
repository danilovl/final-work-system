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

namespace App\Domain\MediaType\Bus\Query\MediaType;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\MediaType\Facade\MediaTypeFacade;

readonly class GetMediaTypeQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MediaTypeFacade $mediaTypeFacade) {}

    public function __invoke(GetMediaTypeQuery $query): GetMediaTypeQueryResult
    {
        $mediaType = $this->mediaTypeFacade->findById($query->id);

        return new GetMediaTypeQueryResult($mediaType);
    }
}
