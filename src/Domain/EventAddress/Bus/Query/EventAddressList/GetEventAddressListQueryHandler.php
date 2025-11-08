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

namespace App\Domain\EventAddress\Bus\Query\EventAddressList;

use App\Application\Service\PaginatorService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetEventAddressListQueryHandler
{
    public function __construct(private PaginatorService $paginatorService) {}

    public function __invoke(GetEventAddressListQuery $query): GetEventAddressListQueryResult
    {
        $pagination = $this->paginatorService->createPaginationRequest(
            $query->request,
            $query->user->getEventAddressOwner()
        );

        return new GetEventAddressListQueryResult($pagination);
    }
}
