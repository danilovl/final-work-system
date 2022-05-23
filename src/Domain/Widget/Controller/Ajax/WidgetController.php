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

namespace App\Domain\Widget\Controller\Ajax;

use App\Domain\Widget\Http\Ajax\WidgetLiveHandle;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WidgetController
{
    public function __construct(private readonly WidgetLiveHandle $widgetLiveHandle)
    {
    }

    public function live(): StreamedResponse
    {
        return $this->widgetLiveHandle->handle();
    }
}
