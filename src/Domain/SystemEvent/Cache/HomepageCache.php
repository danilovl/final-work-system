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
use Danilovl\OpenTelemetryBundle\OpenTelemetry\Interfaces\TracingSpanServiceInterface;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class HomepageCache
{
    private const string HOMEPAGE_ROUTE = 'homepage';

    public function __construct(
        private readonly AdapterInterface $cache,
        private readonly ParameterServiceInterface $parameterService,
        private readonly PaginatorService $paginatorService,
        private readonly SystemEventRecipientFacade $systemEventRecipientFacade,
        private readonly TracingSpanServiceInterface $tracingSpanService
    ) {}

    /**
     * @return array<int, PaginationInterface>
     */
    public function createHomepagePaginator(User $user, int $page = 1): array
    {
        $span = $this->tracingSpanService->start('CreateHomepagePaginator');

        $cacheKey = sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR->value, $user->getId());

        $cacheItem = $this->cache->getItem($cacheKey);
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
            $this->bindToHomepage($pagination);

            $pagePaginators[$page] = $pagination;

            $cacheItem->set($pagePaginators);
            $cacheItem->expiresAfter($this->parameterService->getInt('cache.homepage_time'));

            $this->cache->save($cacheItem);
        }

        $span->end();

        return $pagePaginators;
    }

    private function bindToHomepage(PaginationInterface $pagination): void
    {
        if (!$pagination instanceof SlidingPagination) {
            return;
        }

        $pagination->setUsedRoute(self::HOMEPAGE_ROUTE);

        foreach (array_keys($pagination->getParams()) as $name) {
            $pagination->setParam($name, null);
        }
    }
}
