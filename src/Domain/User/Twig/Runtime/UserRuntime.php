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

namespace App\Domain\User\Twig\Runtime;

use App\Application\Service\S3ClientService;
use App\Domain\User\Entity\User;
use App\Domain\User\Helper\UserRoleHelper;
use App\Domain\User\Service\UserService;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\{
    AbstractExtension,
    RuntimeExtensionInterface
};

class UserRuntime extends AbstractExtension implements RuntimeExtensionInterface
{
    private array $cache = [];

    public function __construct(
        private readonly UserService $userService,
        private readonly RouterInterface $router,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly ParameterServiceInterface $parameterService,
        private readonly S3ClientService $s3ClientService
    ) {}

    public function appUser(): ?User
    {
        return $this->userService->getUserOrNull();
    }

    public function isUserRole(User $user, string $method): bool
    {
        return UserRoleHelper::$method($user);
    }

    public function profileImage(Environment $env, ?User $user): string
    {
        $cacheKey = $user?->getId() ?? 'null';

        $cacheItem = $this->cache[$cacheKey] ?? null;
        if ($cacheItem !== null) {
            return $cacheItem;
        }

        $defaultImagePath = $this->parameterService->getString('default_user_image');
        $imageProfile = $user?->getProfileImage();

        if ($imageProfile !== null) {
            $isFileExist = $this->s3ClientService->doesObjectExist(
                $imageProfile->getType()->getFolder(),
                $imageProfile->getMediaName()
            );

            if ($isFileExist) {
                $url = $this->router->generate('profile_image', [
                    'id' => $this->hashidsService->encode($user->getId())
                ]);

                $this->cache[$user->getId()] = $url;

                return $url;
            }
        }

        $url = $env->getExtension(AssetExtension::class)->getAssetUrl($defaultImagePath);
        $this->cache[$cacheKey] = $url;

        return $url;
    }
}
