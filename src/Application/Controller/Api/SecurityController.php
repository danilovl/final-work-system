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
use App\Application\Service\{
    UserService,
    EntityManagerService
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly UserService $userService
    ) {}

    public function generateToken(): JsonResponse
    {
        $token = HashHelper::generateDefaultHash();
        $user = $this->userService->getUser();
        $user->setToken($token);

        $this->entityManagerService->flush($user);

        return new JsonResponse(['token' => $token]);
    }
}
