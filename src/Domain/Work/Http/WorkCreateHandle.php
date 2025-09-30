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

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Work\Bus\Command\CreateWork\CreateWorkCommand;
use App\Domain\Work\Entity\Work;
use App\Application\Constant\{
    ControllerMethodConstant,
    FlashTypeConstant
};
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\Service\UserService;
use App\Domain\Work\Form\Factory\WorkFormFactory;
use App\Domain\Work\Model\WorkModel;
use App\Domain\WorkDeadline\Facade\WorkDeadlineFacade;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class WorkCreateHandle
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
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $workModel = new WorkModel;
        $workModel->supervisor = $user;

        $form = $this->workFormFactory
            ->getWorkForm($user, ControllerMethodConstant::CREATE, $workModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $command = CreateWorkCommand::create($workModel);
                /** @var Work $work */
                $work = $this->commandBus->dispatchResult($command);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('work_detail', [
                    'id' => $this->hashidsService->encode($work->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
        }

        $workDeadLineService = $this->workDeadlineFacade;
        $workDeadLines = $workDeadLineService->getWorkDeadlinesBySupervisor(
            $user,
            $this->parameterService->getInt('pagination.work.deadline_limit')
        );
        $workProgramDeadLines = $workDeadLineService->getWorkProgramDeadlinesBySupervisor(
            $user,
            $this->parameterService->getInt('pagination.work.program_deadline_limit')
        );

        return $this->twigRenderService->renderToResponse('domain/work/work.html.twig', [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.work_create'),
            'workDeadlines' => $workDeadLines->toArray(),
            'workProgramDeadlines' => $workProgramDeadLines->toArray(),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create')
        ]);
    }
}
