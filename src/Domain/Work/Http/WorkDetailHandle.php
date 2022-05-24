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

namespace App\Domain\Work\Http;

use App\Application\Constant\TabTypeConstant;
use App\Application\Form\Factory\FormDeleteFactory;
use App\Application\Service\{
    UserService,
    SeoPageService,
    TwigRenderService
};
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Service\WorkDetailTabService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class WorkDetailHandle
{
    public function __construct(
        private readonly UserService $userService,
        private readonly WorkDetailTabService $workDetailTabService,
        private readonly ConversationMessageFacade $conversationMessageFacade,
        private readonly SeoPageService $seoPageService,
        private readonly TwigRenderService $twigRenderService,
        private readonly FormDeleteFactory $deleteFactory
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
