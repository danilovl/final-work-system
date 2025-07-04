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

namespace App\Domain\SystemEvent\Cache;

use App\Application\Constant\CacheKeyConstant;
use App\Infrastructure\Service\PaginatorService;
use App\Domain\SystemEvent\Facade\SystemEventRecipientFacade;
use App\Domain\SystemEvent\Helper\SystemEventHelper;
use App\Domain\User\Entity\User;
use App\Infrastructure\OpenTelemetry\Helper\TracingSpan;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class HomepageCache
{
    public function __construct(
        private readonly AdapterInterface $cache,
        private readonly ParameterServiceInterface $parameterService,
        private readonly PaginatorService $paginatorService,
        private readonly SystemEventRecipientFacade $systemEventRecipientFacade
    ) {}

    /**
     * @return array<int, PaginationInterface>
     */
    public function createHomepagePaginator(User $user, int $page = 1): array
    {
        $span = TracingSpan::start('CreateHomepagePaginator');

        $cacheItem = $this->cache->getItem(
            sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR->value, $user->getId())
        );
        /** @var array<int, PaginationInterface>|null $pagePaginators */
        $pagePaginators = $cacheItem->get();

        if (!$cacheItem->isHit() || empty($pagePaginators[$page])) {
            $systemEventsQuery = $this->systemEventRecipientFacade
                ->queryRecipientsQueryByUser($user);

            $pagination = $this->paginatorService->createPagination(
                $systemEventsQuery,
                $page,
                $this->parameterService->getInt('pagination.home.limit')
            );

            $pagination->setItems(SystemEventHelper::groupSystemEventByType($pagination));

            $pagePaginators[$page] = $pagination;

            $cacheItem->set($pagePaginators);
            $cacheItem->expiresAfter($this->parameterService->getInt('cache.homepage_time'));

            $this->cache->save($cacheItem);
        }

        $span->end();

        return $pagePaginators;
    }
}
