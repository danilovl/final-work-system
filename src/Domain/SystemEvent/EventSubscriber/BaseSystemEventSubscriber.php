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

namespace App\Domain\SystemEvent\EventSubscriber;

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\SystemEventType\Bus\Query\SystemEventType\{GetSystemEventTypeQuery, GetSystemEventTypeQueryResult};
use App\Domain\SystemEventType\Entity\SystemEventType;
use App\Infrastructure\Service\EntityManagerService;

class BaseSystemEventSubscriber
{
    public function __construct(
        protected EntityManagerService $entityManagerService,
        protected QueryBusInterface $queryBus
    ) {}

    protected function getSystemEventType(int $id): SystemEventType
    {
        $getSystemEventTypeQuery = GetSystemEventTypeQuery::create($id);
        /** @var GetSystemEventTypeQueryResult $getSystemEventTypeQueryResult */
        $getSystemEventTypeQueryResult = $this->queryBus->handle($getSystemEventTypeQuery);

        return $getSystemEventTypeQueryResult->systemEventType;
    }
}
