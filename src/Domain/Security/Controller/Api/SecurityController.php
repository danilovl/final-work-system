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

namespace App\Domain\Security\Controller\Api;

use App\Application\Helper\HashHelper;
use App\Infrastructure\Service\EntityManagerService;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\User\Bus\Query\UserByUsername\{GetUserByUsernameQuery, GetUserByUsernameQueryResult};

readonly class SecurityController
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private QueryBusInterface $queryBus,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {}

    public function generateToken(Request $request): JsonResponse
    {
        $username = $request->getPayload()->getString('username');
        $password = $request->getPayload()->getString('password');

        $getUserByUsernameQuery = GetUserByUsernameQuery::create($username);
        /** @var GetUserByUsernameQueryResult $getUserByUsernameQueryResult */
        $getUserByUsernameQueryResult = $this->queryBus->handle($getUserByUsernameQuery);
        $user = $getUserByUsernameQueryResult->user;
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
