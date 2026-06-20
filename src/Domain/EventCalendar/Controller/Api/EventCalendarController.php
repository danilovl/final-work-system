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

namespace App\Domain\EventCalendar\Controller\Api;

use App\Domain\EventCalendar\DTO\Api\Input\EventCalendarGetEventInput;
use App\Domain\EventCalendar\Http\Api\EventCalendarGetEventHandle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

readonly class EventCalendarController
{
    public function __construct(private EventCalendarGetEventHandle $eventCalendarGetEventHandle) {}

    public function getEvent(
        #[MapQueryString] EventCalendarGetEventInput $input,
        string $type
    ): JsonResponse {
        return $this->eventCalendarGetEventHandle->__invoke($type, $input->start, $input->end);
    }
}
