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

namespace App\Model\Work\Http;

use App\Constant\TabTypeConstant;
use App\Entity\Work;
use App\Model\Conversation\Facade\ConversationMessageFacade;
use App\Model\Work\Service\WorkDetailTabService;
use App\Form\Factory\FormDeleteFactory;
use App\Service\{
    UserService,
    SeoPageService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class WorkDetailHandle
{
    public function __construct(
        private UserService $userService,
        private WorkDetailTabService $workDetailTabService,
        private ConversationMessageFacade $conversationMessageFacade,
        private SeoPageService $seoPageService,
        private TwigRenderService $twigRenderService,
        private FormDeleteFactory $deleteFactory
    ) {
    }

    public function handle(Request $request, Work $work): Response
    {
        $user = $this->userService->getUser();
        $tabService = $this->workDetailTabService
            ->setActiveTab($request->query->get('tab'));

        $paginationTask = $tabService->getTabPagination($request, TabTypeConstant::TAB_TASK, $work, $user);
        $paginationVersion = $tabService->getTabPagination($request, TabTypeConstant::TAB_VERSION, $work);
        $paginationEvent = $tabService->getTabPagination($request, TabTypeConstant::TAB_EVENT, $work);
        $paginationMessage = $tabService->getTabPagination($request, TabTypeConstant::TAB_MESSAGE, $work, $user);

        $this->conversationMessageFacade->setIsReadToConversationMessages($paginationMessage, $user);

        $this->seoPageService->setTitle($work->getTitle());

        return $this->twigRenderService->render('work/detail.html.twig', [
            'work' => $work,
            'tasks' => $paginationTask,
            'versions' => $paginationVersion,
            'messages' => $paginationMessage,
            'events' => $paginationEvent,
            'activeTab' => $tabService->getActiveTab(),
            'deleteForm' => $this->deleteFactory->createDeleteForm($work, 'work_delete')->createView()
        ]);
    }
}
