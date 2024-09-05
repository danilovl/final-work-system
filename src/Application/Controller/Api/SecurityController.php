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
use App\Domain\User\Facade\UserFacade;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

readonly class SecurityController
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserFacade $userFacade,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {}

    public function generateToken(Request $request): JsonResponse
    {
        $username = $request->request->getString('username');
        $password = $request->request->getString('password');

        $user = $this->userFacade->findOneByUsername($username);
        if ($user === null) {
            throw new CustomUserMessageAuthenticationException('User could not be found.');
        }

        $isPasswordValid = $this->userPasswordHasher->isPasswordValid($user, $password);
        if (!$isPasswordValid) {
            throw new CustomUserMessageAuthenticationException('User password is invalid.');
        }

        $token = HashHelper::generateDefaultHash();
        $user->setToken($token);

        $this->entityManagerService->flush();

        return new JsonResponse(['token' => $token]);
    }
}
