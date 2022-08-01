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

namespace App\Domain\User\Http\Api;

use App\Application\Service\UserService;
use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserDetailHandle
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ObjectToArrayTransformService $objectToArrayTransformService
    ) {}

    public function handle(): JsonResponse
    {
        $user = $this->userService->getUser();
        $data = $this->objectToArrayTransformService->transform('api_key_field', $user);

        return new JsonResponse($data);
    }
}
