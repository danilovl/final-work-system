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

namespace App\Domain\SystemEventRecipient\Bus\Query\UnreadSystemEventsByRecipient;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\SystemEventRecipient\Facade\SystemEventRecipientFacade;

readonly class GetUnreadSystemEventsByRecipientQueryHandler implements QueryHandlerInterface
{
    public function __construct(private SystemEventRecipientFacade $systemEventRecipientFacade) {}

    public function __invoke(GetUnreadSystemEventsByRecipientQuery $query): GetUnreadSystemEventsByRecipientQueryResult
    {
        $systemEventRecipients = $this->systemEventRecipientFacade->getUnreadSystemEventsByRecipient(
            user: $query->user,
            limit: $query->limit
        );

        return new GetUnreadSystemEventsByRecipientQueryResult($systemEventRecipients);
    }
}
