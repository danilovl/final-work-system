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

use App\Helper\SystemEventHelper;
use Danilovl\PermissionMiddlewareBundle\Annotation\PermissionMiddleware;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class HomeController extends BaseController
{
    /**
     * @PermissionMiddleware(
     *      date={
     *          "from": "31-01-2020"
     *      }
     * )
     */
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $systemEventsQuery = $this->get('app.facade.system_event_recipient')
            ->queryRecipientsQueryByUser($user);

        $isUnreadExist = $this->get('app.facade.system_event')
            ->isUnreadSystemEventsByRecipient($user);

        $pagination = $this->createPagination(
            $request,
            $systemEventsQuery,
            $this->getParam('pagination.default.page'),
            $this->getParam('pagination.home.limit')
        );

        $systemEventPagination = $pagination;
        $pagination->setItems(SystemEventHelper::groupSystemEventByType($pagination));

        return $this->render('home/index.html.twig', [
            'isSystemEventUnreadExist' => $isUnreadExist,
            'systemEventGroup' => $pagination,
            'systemEventPagination' => $systemEventPagination
        ]);
    }
}
