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

namespace App\Middleware\EventCalendar\Ajax;

use App\Constant\DateFormatConstant;
use App\Middleware\Interfaces\RequestMiddlewareInterface;
use App\Exception\AjaxRuntimeException;
use App\Helper\DateHelper;
use Symfony\Component\HttpFoundation\Request;

class GetEventMiddleware implements RequestMiddlewareInterface
{
    public static function handle(Request $request): bool
    {
        $type = $request->attributes->get('type');
        $startDate = $request->request->get('start');
        $endDate = $request->request->get('end');

        if (empty($type)) {
            throw new AjaxRuntimeException('Empty type');
        }

        if (DateHelper::validateDate(DateFormatConstant::DATE_TIME, $startDate) === false ||
            DateHelper::validateDate(DateFormatConstant::DATE_TIME, $endDate) === false
        ) {
            throw new AjaxRuntimeException('Bad format date');
        }

        if ($startDate > $endDate) {
            throw new AjaxRuntimeException('StartDate must be less then endDate');
        }

        return true;
    }
}
