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

namespace App\Domain\SystemEvent\Controller\Api;

use App\Domain\SystemEvent\Http\Api\SystemEventTypeEventsHandle;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class SystemEventController
{
    public function __construct(
        private SystemEventTypeEventsHandle $systemEventTypeEventsHandle
    ) {}

    public function list(Request $request, string $type): JsonResponse
    {
        return $this->systemEventTypeEventsHandle->__invoke($request, $type);
    }
}
