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
use App\Application\Provider\ElasticApmProvider;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ResponseListener implements EventSubscriberInterface
{
    public function __construct(private ElasticApmProvider $elasticApmProvider) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse'
        ];
    }

    public function onKernelResponse(): void
    {
        if (!$this->elasticApmProvider->isEnable()) {
            return;
        }

        ElasticApmHelper::endCurrentSpan();
    }
}
