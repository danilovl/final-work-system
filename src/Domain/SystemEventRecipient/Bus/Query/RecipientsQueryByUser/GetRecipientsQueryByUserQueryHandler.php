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

namespace App\Domain\SystemEventRecipient\Bus\Query\RecipientsQueryByUser;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\SystemEventRecipient\Facade\SystemEventRecipientFacade;

readonly class GetRecipientsQueryByUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(private SystemEventRecipientFacade $systemEventRecipientFacade) {}

    public function __invoke(GetRecipientsQueryByUserQuery $query): GetRecipientsQueryByUserQueryResult
    {
        $query = $this->systemEventRecipientFacade->queryRecipientsQueryByUser($query->recipient);

        return new GetRecipientsQueryByUserQueryResult($query);
    }
}
