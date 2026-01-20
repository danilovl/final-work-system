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

namespace App\Domain\Widget\Http\Ajax;

use App\Domain\Widget\Service\WidgetStreamService;
use Symfony\Component\HttpFoundation\EventStreamResponse;

readonly class WidgetLiveHandle
{
    public function __construct(private WidgetStreamService $widgetStreamService) {}

    public function __invoke(): EventStreamResponse
    {
        $callback = $this->widgetStreamService->handle();

        return new EventStreamResponse($callback);
    }
}
