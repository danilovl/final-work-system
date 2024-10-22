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

namespace App\Application\EventListener;

use App\Application\ElasticApm\ElasticApmHelper;
use Elastic\Apm\ElasticApm;
use App\Application\Service\{
    SeoPageService,
    EntityManagerService
};
use App\Domain\User\Service\UserService;
use Danilovl\AsyncBundle\Service\AsyncService;
use DateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class RequestListener implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private SeoPageService $seoPageService,
        private AsyncService $asyncService
    ) {}

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        $this->elasticApmDiscard($requestEvent);

        if (!$requestEvent->isMainRequest()) {
            return;
        }

        $context = $requestEvent->getRequest()->attributes;
        $context = iterator_to_array($context->getIterator());
        ElasticApmHelper::addContextToCurrentTransaction($context, 'request');

        $this->defaultRouteSeoPage($requestEvent);

        $this->asyncService->add(function (): void {
            $this->userLastRequestedAt();
        });
    }

    private function userLastRequestedAt(): void
    {
        $user = $this->userService->getUserOrNull();
        if ($user === null) {
            return;
        }

        $user->setLastRequestedAt(new DateTime);
        $this->entityManagerService->flush();
    }

    private function defaultRouteSeoPage(RequestEvent $requestEvent): void
    {
        /** @var array{title: string}|null $seo */
        $seo = $requestEvent->getRequest()->attributes->get('_seo');
        $this->seoPageService->setTitle($seo['title'] ?? null);
    }

    private function elasticApmDiscard(RequestEvent $requestEvent): void
    {
        $isProfiler = $requestEvent->getRequest()->attributes->get('_route') == '_wdt';

        if (!$requestEvent->isMainRequest() || $isProfiler) {
            ElasticApm::getCurrentTransaction()->discard();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
