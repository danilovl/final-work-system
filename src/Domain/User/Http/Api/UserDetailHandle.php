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

use App\Application\Mapper\ObjectToDtoMapper;
use App\Domain\User\DTO\Api\UserDetailDTO;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class UserDetailHandle
{
    public function __construct(
        private UserService $userService,
        private ObjectToDtoMapper $objectToDtoMapper
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->userService->getUser();
        $userDTO = $this->objectToDtoMapper->map($user, UserDetailDTO::class);

        return new JsonResponse($userDTO);
    }
}
