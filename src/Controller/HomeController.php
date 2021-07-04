<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Controller;

use App\Constant\CacheKeyConstant;
use App\Helper\SystemEventHelper;
use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class HomeController extends BaseController
{
    #[PermissionMiddleware(['date' => ['from' => '31-01-2020']])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $page = $request->query->getInt('page', $this->getParam('pagination.default.page'));

        $cacheItem = $this->get('cache.app')->getItem(
            sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR, $user->getId())
        );
        $pagePaginators = $cacheItem->get();

        $isUnreadExist = $this->get('app.facade.system_event')
            ->isUnreadSystemEventsByRecipient($user);

        if (!$cacheItem->isHit() || empty($pagePaginators[$page])) {
            $systemEventsQuery = $this->get('app.facade.system_event_recipient')
                ->queryRecipientsQueryByUser($user);

            $pagination = $this->createPagination(
                $request,
                $systemEventsQuery,
                $page,
                $this->getParam('pagination.home.limit')
            );

            $pagination->setItems(SystemEventHelper::groupSystemEventByType($pagination));

            $pagePaginators[$page] = $pagination;

            $cacheItem->set($pagePaginators);
            $cacheItem->expiresAfter($this->getParam('cache.homepage_time'));

            $this->get('cache.app')->save($cacheItem);
        }

        return $this->render('home/index.html.twig', [
            'isSystemEventUnreadExist' => $isUnreadExist,
            'paginator' => $pagePaginators[$page]
        ]);
    }
}
