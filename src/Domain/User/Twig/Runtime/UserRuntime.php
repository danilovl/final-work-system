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

use App\Application\Helper\UserRoleHelper;
use App\Application\Service\UserService;
use App\Domain\User\Entity\User;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\RuntimeExtensionInterface;

class UserRuntime extends AbstractExtension implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ParameterServiceInterface $parameterService
    ) {
    }

    public function appUser(): ?User
    {
        return $this->userService->getUser();
    }

    public function isUserRole(User $user, string $method): bool
    {
        return UserRoleHelper::$method($user);
    }

    public function profileImage(Environment $env, ?User $user): string
    {
        $defaultImagePath = $this->parameterService->getString('default_user_image');

        if ($user !== null) {
            $imageProfile = $user->getProfileImage();
            if ($imageProfile !== null && $imageProfile->existMediaFile()) {
                $defaultImagePath = $imageProfile->getWebPath();
            }
        }

        return $env->getExtension(AssetExtension::class)->getAssetUrl($defaultImagePath);
    }
}
