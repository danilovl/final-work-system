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
use LogicException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\{
    HandleTrait,
    MessageBusInterface
};

class MessengerQueryBus implements QueryBusInterface
{
    use HandleTrait {
        handle as private handleMessage;
    }

    public function __construct(
        #[Target('query.bus')]
        private MessageBusInterface $messageBus
    ) {}

    public function handle(QueryInterface $query): object
    {
        $result = $this->handleMessage($query);

        if (!is_object($result)) {
            $message = sprintf('Query "%s" handler must return an object, "%s" returned.', $query::class, get_debug_type($result));

            throw new LogicException($message);
        }

        return $result;
    }
}
