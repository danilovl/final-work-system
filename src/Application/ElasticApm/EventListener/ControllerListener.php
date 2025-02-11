<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\ElasticApm\EventListener;

use App\Application\ElasticApm\{
    ElasticApmHelper,
    SpanNameEnum,
    SpanTypeEnum
};
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ControllerListener implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController'
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        ElasticApmHelper::beginSpanWithContext(
            SpanNameEnum::HANDLE,
            SpanTypeEnum::CONTROLLER
        );
    }
}
