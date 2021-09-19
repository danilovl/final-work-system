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

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class EventCalendarController extends BaseController
{
    public function reservation(): Response
    {
        return $this->get('app.http_handle.event_calendar.reservation')->handle();
    }

    public function manage(): Response
    {
        return $this->get('app.http_handle.event_calendar.manage')->handle();
    }
}
