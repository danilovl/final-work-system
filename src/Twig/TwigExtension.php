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

use App\Twig\Runtime\AwayRuntime;
use Twig\{
    TwigFilter,
    TwigFunction
};
use App\Services\SystemEventLinkGeneratorService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;

class TwigExtension extends AbstractExtension
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('systemEventGenerateLink', [SystemEventLinkGeneratorService::class, 'generateLink'])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('away_to', [AwayRuntime::class, 'to'], ['is_safe' => ['html']]),
        ];
    }
}
