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
use App\Domain\Profile\Http\Api\ProfileChangePasswordHandle;
use App\Domain\Profile\Http\ProfileImageHandle;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
    BinaryFileResponse
};

#[OA\Tag(name: 'User')]
readonly class ProfileController
{
    public function __construct(
        private ProfileImageHandle $profileImageHandle,
        private ProfileChangePasswordHandle $profileChangePasswordHandle
    ) {}

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

    #[OA\Put(
        path: '/api/key/users/password',
        description: 'Changes the password of the current authenticated user. The current API token stays valid. Error messages are translated by the Accept-Language header, falling back to the user locale.',
        summary: 'Change password'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['currentPassword', 'newPassword'],
            properties: [
                new OA\Property(property: 'currentPassword', type: 'string', example: 'old-secret'),
                new OA\Property(property: 'newPassword', type: 'string', minLength: 6, example: 'new-secret')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 204,
        description: 'Password changed (No Content)'
    )]
    #[OA\Response(
        response: 400,
        description: 'Malformed JSON (code "invalid_request"), missing or empty field (code "missing_field") or wrong current password (code "invalid_current_password")',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', type: 'string', example: 'invalid_current_password'),
                new OA\Property(property: 'message', type: 'string', example: 'The current password is incorrect.')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'New password does not satisfy the password rules (code "invalid_new_password")',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', type: 'string', example: 'invalid_new_password'),
                new OA\Property(property: 'message', type: 'string', example: 'Your password should be at least 6 characters.')
            ],
            type: 'object'
        )
    )]
    public function changePassword(Request $request): JsonResponse
    {
        return $this->profileChangePasswordHandle->__invoke($request);
    }
}
