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

namespace App\Cache;

use App\Constant\CacheKeyConstant;
use App\Entity\User;
use App\Helper\SystemEventHelper;
use App\Model\SystemEvent\SystemEventRecipientFacade;
use App\Service\PaginatorService;
use Danilovl\ParameterBundle\Services\ParameterService;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class HomepageCache
{
    public function __construct(
        private AdapterInterface $cache,
        private ParameterService $parameterService,
        private PaginatorService $paginatorService,
        private SystemEventRecipientFacade $systemEventRecipientFacade
    ) {
    }

    public function createHomepagePaginator(
        User $user,
        int $page = 1
    ): array {
        $cacheItem = $this->cache->getItem(
            sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR, $user->getId())
        );
        $pagePaginators = $cacheItem->get();

        if (!$cacheItem->isHit() || empty($pagePaginators[$page])) {
            $systemEventsQuery = $this->systemEventRecipientFacade
                ->queryRecipientsQueryByUser($user);

            $pagination = $this->paginatorService->createPagination(
                $systemEventsQuery,
                $page,
                $this->parameterService->get('pagination.home.limit')
            );

            $pagination->setItems(SystemEventHelper::groupSystemEventByType($pagination));

            $pagePaginators[$page] = $pagination;

            $cacheItem->set($pagePaginators);
            $cacheItem->expiresAfter($this->parameterService->get('cache.homepage_time'));

            $this->cache->save($cacheItem);
        }

        return $pagePaginators;
    }
}
