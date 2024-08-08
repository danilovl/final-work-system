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

namespace App\Application\Controller\Api;

use App\Application\Helper\HashHelper;
use App\Application\Service\EntityManagerService;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class SecurityController
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserService $userService
    ) {}

    public function generateToken(): JsonResponse
    {
        $token = HashHelper::generateDefaultHash();
        $user = $this->userService->getUser();
        $user->setToken($token);

        $this->entityManagerService->flush();

        return new JsonResponse(['token' => $token]);
    }
}
