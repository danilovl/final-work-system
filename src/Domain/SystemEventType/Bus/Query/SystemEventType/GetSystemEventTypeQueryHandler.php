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

namespace App\Domain\SystemEventType\Bus\Query\SystemEventType;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\SystemEventType\Facade\SystemEventTypeFacade;

readonly class GetSystemEventTypeQueryHandler implements QueryHandlerInterface
{
    public function __construct(private SystemEventTypeFacade $systemEventTypeFacade) {}

    public function __invoke(GetSystemEventTypeQuery $query): GetSystemEventTypeQueryResult
    {
        $systemEventType = $this->systemEventTypeFacade->getById($query->id);

        return new GetSystemEventTypeQueryResult($systemEventType);
    }
}
