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

namespace App\Domain\Media\Bus\Query\MediaFile;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Media\Service\MediaService;

readonly class GetMediaFileQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MediaService $mediaService) {}

    public function __invoke(GetMediaFileQuery $query): GetMediaFileQueryResult
    {
        $response = $this->mediaService->download($query->media);

        return new GetMediaFileQueryResult($response);
    }
}
