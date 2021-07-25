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

        $pagePaginators = $this->get('app.cache.homepage')->createHomepagePaginator(
            $user,
            $page
        );

        $isUnreadExist = $this->get('app.facade.system_event')
            ->isUnreadSystemEventsByRecipient($user);

        return $this->render('home/index.html.twig', [
            'isSystemEventUnreadExist' => $isUnreadExist,
            'paginator' => $pagePaginators[$page]
        ]);
    }
}
