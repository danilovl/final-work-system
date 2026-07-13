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

namespace App\Domain\Event\Http\Api;

use App\Application\Mapper\ObjectToDtoMapper;
use App\Domain\Event\DTO\Api\EventDetailDTO;
use App\Domain\Event\Entity\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class EventDetailHandler
{
    public function __construct(private ObjectToDtoMapper $objectToDtoMapper) {}

    public function __invoke(Event $event): JsonResponse
    {
        $ignoreAttributes = ['user:read:author', 'user:read:supervisor', 'user:read:opponent', 'user:read:consultant'];
        $eventDetailDTO = $this->objectToDtoMapper->map($event, EventDetailDTO::class, $ignoreAttributes);

        return new JsonResponse($eventDetailDTO);
    }
}
