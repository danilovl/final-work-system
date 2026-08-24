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

namespace App\Domain\Profile\Controller\Api;

use App\Domain\User\Entity\User;
use App\Domain\Profile\Http\ProfileImageHandle;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\{
    Response,
    BinaryFileResponse
};

#[OA\Tag(name: 'User')]
readonly class ProfileController
{
    public function __construct(private ProfileImageHandle $profileImageHandle) {}

    #[OA\Get(
        path: '/api/key/users/{id}/profile/image',
        description: 'Returns the profile image file for the given user ID.',
        summary: 'Get user profile image'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'User identifier (hashid).',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Binary image file or empty response when no image exists',
        content: new OA\MediaType(
            mediaType: 'image/*'
        )
    )]
    public function image(User $user): BinaryFileResponse|Response
    {
        return $this->profileImageHandle->__invoke($user);
    }
}
