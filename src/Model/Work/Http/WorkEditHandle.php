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

use App\Entity\Work;
use App\Model\Work\EventDispatcher\WorkEventDispatcherService;
use App\Model\Work\Form\Factory\WorkFormFactory;
use App\Constant\{
    FlashTypeConstant,
    ControllerMethodConstant
};
use App\Model\Work\Factory\WorkFactory;
use App\Model\Work\WorkModel;
use App\Model\WorkDeadline\Facade\WorkDeadlineFacade;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use App\Service\{
    UserService,
    RequestService,
    TranslatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class WorkEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private ParameterServiceInterface $parameterService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private HashidsServiceInterface $hashidsService,
        private WorkFormFactory $workFormFactory,
        private WorkDeadlineFacade $workDeadlineFacade,
        private WorkFactory $workFactory,
        private WorkEventDispatcherService $workEventDispatcherService
    ) {
    }

    public function handle(Request $request, Work $work): Response
    {
        $user = $this->userService->getUser();

        $workModel = WorkModel::fromWork($work);
        $form = $this->workFormFactory
            ->getWorkForm($user, ControllerMethodConstant::EDIT, $workModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->workFactory->flushFromModel($workModel, $work);
                $this->workEventDispatcherService->onWorkEdit($work);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('work_edit', [
                    'id' => $this->hashidsService->encode($work->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        $workDeadLineService = $this->workDeadlineFacade;
        $workDeadLines = $workDeadLineService
            ->getWorkDeadlinesBySupervisor(
                $user,
                $this->parameterService->get('pagination.work.deadline_limit')
            )->toArray();

        $workProgramDeadLines = $workDeadLineService
            ->getWorkProgramDeadlinesBySupervisor(
                $user,
                $this->parameterService->get('pagination.work.program_deadline_limit')
            )->toArray();

        if ($request->isXmlHttpRequest()) {
            $form = $this->workFormFactory->getWorkForm(
                $user,
                ControllerMethodConstant::EDIT_AJAX,
                $workModel,
                $work
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'work/work.html.twig');

        return $this->twigRenderService->render($template, [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.work_edit'),
            'workDeadlines' => $workDeadLines,
            'workProgramDeadlines' => $workProgramDeadLines,
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
