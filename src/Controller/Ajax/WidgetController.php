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

namespace App\Controller\Ajax;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WidgetController extends BaseController
{
    public function live(): StreamedResponse
    {
        return $this->get('app.http_handle_ajax.widget.live')->handle();
    }
}
