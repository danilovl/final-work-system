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

namespace App\Domain\EventAddress\Bus\Query\EventAddress;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\EventAddress\Facade\EventAddressFacade;

readonly class GetEventAddressQueryHandler implements QueryHandlerInterface
{
    public function __construct(private EventAddressFacade $eventAddressFacade) {}

    public function __invoke(GetEventAddressQuery $query): GetEventAddressQueryResult
    {
        $eventAddress = $this->eventAddressFacade->findById($query->id);

        return new GetEventAddressQueryResult($eventAddress);
    }
}
