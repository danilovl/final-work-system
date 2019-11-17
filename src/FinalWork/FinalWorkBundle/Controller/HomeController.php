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

namespace FinalWork\FinalWorkBundle\Controller;

use FinalWork\FinalWorkBundle\Helper\SystemEventHelper;
use LogicException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class HomeController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @throws LogicException
     */
    public function indexAction(Request $request): Response
    {
        $stopwatch = $this->get('debug.stopwatch');
        $stopwatch->start('dashboardAlerts');

        $systemEventsQuery = $this->get('final_work.facade.system_event_recipient')
            ->queryRecipientsQueryByUser($this->getUser());

        $pagination = $this->createPagination(
            $request,
            $systemEventsQuery,
            $this->getParam('pagination.default.page'),
            $this->getParam('pagination.home.limit')
        );

        $systemEventPagination = $pagination;
        $pagination->setItems(SystemEventHelper::groupSystemEventByType($pagination));

        $stopwatch->stop('dashboardAlerts');
        $this->get('final_work.seo_page')->setTitle('finalwork.page.home');

        return $this->render('@FinalWork/home/index.html.twig', [
            'systemEventGroup' => $pagination,
            'systemEventPagination' => $systemEventPagination
        ]);
    }
}
