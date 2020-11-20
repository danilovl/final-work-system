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

use App\Twig\Runtime\{
    UserRuntime,
    AwayRuntime,
    HomepageNotifyRuntime
};
use Danilovl\ParameterBundle\Services\ParameterService;
use Twig\{
    TwigFilter,
    TwigFunction
};
use App\Services\SystemEventLinkGeneratorService;
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
            new TwigFunction('app_user', [UserRuntime::class, 'appUser']),
            new TwigFunction('system_event_generate_link', [SystemEventLinkGeneratorService::class, 'generateLink']),
            new TwigFunction('homepage_notify', [HomepageNotifyRuntime::class, 'renderNotify'], ['is_safe' => ['html']])
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('away_to', [AwayRuntime::class, 'to'], ['is_safe' => ['html']]),
            new TwigFilter('profile_image', [UserRuntime::class, 'profileImage'], ['needs_environment' => true, 'is_safe' => ['html']])
        ];
    }
}
