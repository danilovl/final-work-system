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

namespace App\Application\Middleware\EventCalendar\Ajax;

use App\Application\Constant\DateFormatConstant;
use App\Application\Exception\AjaxRuntimeException;
use App\Application\Helper\DateHelper;
use App\Application\Interfaces\Middleware\RequestMiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;

class EditMiddleware implements RequestMiddlewareInterface
{
    public static function handle(Request $request): bool
    {
        $startDate = $request->request->getString('start');
        $endDate = $request->request->getString('end');

        if (DateHelper::validateDate(DateFormatConstant::DATE_TIME->value, $startDate) === false ||
            DateHelper::validateDate(DateFormatConstant::DATE_TIME->value, $endDate) === false
        ) {
            throw new AjaxRuntimeException('Bad format date');
        }

        if ($startDate > $endDate) {
            throw new AjaxRuntimeException('StartDate must be less then endDate');
        }

        return true;
    }
}
