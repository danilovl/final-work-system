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

namespace App\Domain\Document\Http;

use App\Domain\Media\Entity\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\Media\Bus\Query\MediaFile\{GetMediaFileQuery, GetMediaFileQueryResult};

readonly class DocumentDownloadHandle
{
    public function __construct(private QueryBusInterface $queryBus) {}

    public function __invoke(Media $media): BinaryFileResponse
    {
        $getMediaFileQuery = GetMediaFileQuery::create($media);
        /** @var GetMediaFileQueryResult $getMediaFileQueryResult */
        $getMediaFileQueryResult = $this->queryBus->handle($getMediaFileQuery);
        $response = $getMediaFileQueryResult->response;

        return $response;
    }
}
