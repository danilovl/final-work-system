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

use App\Application\Util\StreamedResponseUtil;
use App\Domain\Widget\Service\WidgetStreamService;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class WidgetLiveHandle
{
    public function __construct(private WidgetStreamService $widgetStreamService) {}

    public function handle(): StreamedResponse
    {
        $callback = $this->widgetStreamService->handle();

        return StreamedResponseUtil::create($callback);
    }
}
