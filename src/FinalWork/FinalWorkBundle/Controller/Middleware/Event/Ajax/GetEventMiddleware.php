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

namespace FinalWork\FinalWorkBundle\Controller\Middleware\Event\Ajax;

use FinalWork\FinalWorkBundle\Controller\Middleware\Interfaces\RequestMiddlewareInterface;
use FinalWork\FinalWorkBundle\Exception\AjaxRuntimeException;
use FinalWork\FinalWorkBundle\Helper\DateHelper;
use Symfony\Component\HttpFoundation\Request;

class GetEventMiddleware implements RequestMiddlewareInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public static function handle(Request $request): bool
    {
        $startDate = $request->get('start');
        $endDate = $request->get('end');

        if (DateHelper::validateDate('Y-m-d H:i', $startDate) === false ||
            DateHelper::validateDate('Y-m-d H:i', $endDate) === false
        ) {
            throw new AjaxRuntimeException('Bad format date');
        }

        if ($startDate > $endDate) {
            throw new AjaxRuntimeException('StartDate must be less then endDate');
        }

        return true;
    }
}