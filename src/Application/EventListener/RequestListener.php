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

namespace App\Application\EventListener;

use Override;
use App\Application\Service\SeoPageService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class RequestListener implements EventSubscriberInterface
{
    public function __construct(private SeoPageService $seoPageService) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        if (!$requestEvent->isMainRequest()) {
            return;
        }

        $this->defaultRouteSeoPage($requestEvent);
    }

    private function defaultRouteSeoPage(RequestEvent $requestEvent): void
    {
        /** @var array{title: string}|null $seo */
        $seo = $requestEvent->getRequest()->attributes->get('_seo');
        $this->seoPageService->setTitle($seo['title'] ?? null);
    }
}
