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
use Symfony\Component\HttpFoundation\StreamedResponse;

class WidgetLiveHandle
{
    public function __construct(private readonly WidgetStreamService $widgetStreamService)
    {
    }

    public function handle(): StreamedResponse
    {
        $response = new StreamedResponse(
            $this->widgetStreamService->handle()
        );
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');

        return $response;
    }
}
