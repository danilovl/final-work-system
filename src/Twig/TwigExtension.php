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

namespace App\Twig;

use App\Entity\User;
use App\Twig\Runtime\AwayRuntime;
use Danilovl\ParameterBundle\Services\ParameterService;
use Twig\{
    TwigFilter,
    Environment,
    TwigFunction
};
use App\Services\SystemEventLinkGeneratorService;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Twig\Extension\AbstractExtension;

class TwigExtension extends AbstractExtension
{
    private ParameterService $parameterService;

    public function __construct(ParameterService $parameterService)
    {
        $this->parameterService = $parameterService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('systemEventGenerateLink', [SystemEventLinkGeneratorService::class, 'generateLink'])
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('away_to', [AwayRuntime::class, 'to'], ['is_safe' => ['html']]),
            new TwigFilter('profile_image', [$this, 'profileImage'], ['needs_environment' => true, 'is_safe' => ['html']])
        ];
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
