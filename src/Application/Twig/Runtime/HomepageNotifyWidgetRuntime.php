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

namespace App\Application\Twig\Runtime;

use App\Application\Interfaces\Widget\WidgetInterface;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\RuntimeExtensionInterface;

class HomepageNotifyWidgetRuntime extends AbstractExtension implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ParameterServiceInterface $parameterService
    ) {}

    public function renderNotify(): ?string
    {
        $notifyServices = $this->parameterService->getArray('homepage_notify.notifies');

        $result = null;
        foreach ($notifyServices as $notifyService) {
            $notify = $this->container->get($notifyService);
            if (!$notify instanceof WidgetInterface) {
                continue;
            }

            $result .= $notify->render();
        }

        return $result;
    }
}
