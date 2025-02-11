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

use App\Application\ElasticApm\ElasticApmHelper;
use Elastic\Apm\ElasticApm;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class RequestListener implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        $this->elasticApmDiscard($requestEvent);

        if (!$requestEvent->isMainRequest()) {
            return;
        }

        $context = $requestEvent->getRequest()->attributes;
        $context = iterator_to_array($context->getIterator());
        ElasticApmHelper::addContextToCurrentTransaction($context, 'request');
    }

    private function elasticApmDiscard(RequestEvent $requestEvent): void
    {
        $isProfiler = $requestEvent->getRequest()->attributes->get('_route') == '_wdt';

        if (!$requestEvent->isMainRequest() || $isProfiler) {
            ElasticApm::getCurrentTransaction()->discard();
        }
    }
}
