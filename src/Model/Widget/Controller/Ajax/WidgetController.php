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

namespace App\Model\Widget\Controller\Ajax;

use App\Controller\BaseController;
use App\Model\Widget\Http\Ajax\WidgetLiveHandle;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WidgetController extends BaseController
{
    public function __construct(private WidgetLiveHandle $widgetLiveHandle)
    {
    }

    public function live(): StreamedResponse
    {
        return $this->widgetLiveHandle->handle();
    }
}
