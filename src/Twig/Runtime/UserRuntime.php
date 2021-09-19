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

namespace App\Twig\Runtime;

use App\Entity\User;
use App\Helper\UserRoleHelper;
use App\Service\UserService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Twig\Environment;
use Twig\Extension\AbstractExtension;

class UserRuntime extends AbstractExtension
{
    public function __construct(
        private UserService $userService,
        private ParameterServiceInterface $parameterService
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
        $defaultImagePath = $this->parameterService->get('default_user_image');

        if ($user !== null) {
            $imageProfile = $user->getProfileImage();
            if ($imageProfile !== null && $imageProfile->existMediaFile()) {
                $defaultImagePath = $imageProfile->getWebPath();
            }
        }

        return $env->getExtension(AssetExtension::class)->getAssetUrl($defaultImagePath);
    }
}
