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

namespace App\Domain\EventAddress\Bus\Query\SkypeEventAddress;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\EventAddress\Facade\EventAddressFacade;

readonly class GetSkypeEventAddressQueryHandler implements QueryHandlerInterface
{
    public function __construct(private EventAddressFacade $eventAddressFacade) {}

    public function __invoke(GetSkypeEventAddressQuery $query): GetSkypeEventAddressQueryResult
    {
        $eventAddress = $this->eventAddressFacade->findSkypeByOwner($query->user);

        return new GetSkypeEventAddressQueryResult($eventAddress);
    }
}
