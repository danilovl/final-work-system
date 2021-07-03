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

use App\Interfaces\WidgetInterface;
use Danilovl\ParameterBundle\Services\ParameterService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;

class HomepageNotifyWidgetRuntime extends AbstractExtension
{
    public function __construct(
        private ContainerInterface $container,
        private ParameterService $parameterService
    ) {
    }

    public function renderNotify(): ?string
    {
        $notifyServices = $this->parameterService->get('homepage_notify.notifies');

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
