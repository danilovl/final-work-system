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

namespace App\Infrastructure\Messaging;

use App\Application\Interfaces\Bus\{
    QueryBusInterface,
    QueryInterface
};
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class MessengerQueryBus implements QueryBusInterface
{
    public function __construct(private MessageBusInterface $queryBus) {}

    public function handle(QueryInterface $query): object
    {
        $envelope = $this->queryBus->dispatch($query);
        $handledStamps = $envelope->all(HandledStamp::class);

        /** @var object $result */
        $result = $handledStamps[0]->getResult();

        return $result;
    }
}
