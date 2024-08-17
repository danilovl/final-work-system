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

namespace App\Domain\Event\Controller\Api;

use App\Domain\Event\Http\Api\EventListHandle;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class EventController
{
    public function __construct(private EventListHandle $eventListHandle) {}

    public function list(Request $request, Work $work): JsonResponse
    {
        return $this->eventListHandle->handle($request, $work);
    }
}
