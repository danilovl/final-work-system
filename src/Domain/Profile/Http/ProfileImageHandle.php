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

namespace App\Domain\Profile\Http;

use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\{
    Response,
    BinaryFileResponse
};
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\Media\Bus\Query\MediaFile\{GetMediaFileQuery, GetMediaFileQueryResult};

readonly class ProfileImageHandle
{
    public function __construct(private QueryBusInterface $queryBus) {}

    public function __invoke(User $user): BinaryFileResponse|Response
    {
        $profileImage = $user->getProfileImage();
        if ($profileImage === null) {
            return new Response(status: Response::HTTP_NO_CONTENT);
        }

        $getMediaFileQuery = GetMediaFileQuery::create($profileImage);
        /** @var GetMediaFileQueryResult $getMediaFileQueryResult */
        $getMediaFileQueryResult = $this->queryBus->handle($getMediaFileQuery);
        $response = $getMediaFileQueryResult->response;

        return $response;
    }
}
