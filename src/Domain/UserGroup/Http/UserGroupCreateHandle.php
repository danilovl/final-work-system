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

namespace App\Domain\UserGroup\Http;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\UserGroup\Bus\Command\CreateUserGroup\CreateUserGroupCommand;
use App\Domain\UserGroup\Form\Factory\UserGroupFormFactory;
use App\Domain\UserGroup\Model\UserGroupModel;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class UserGroupCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private CommandBusInterface $commandBus,
        private UserGroupFormFactory $userGroupFormFactory
    ) {}

    public function __invoke(Request $request): Response
    {
        $userGroupModel = new UserGroupModel;
        $userGroupModel->name = 'Name';

        $form = $this->userGroupFormFactory
            ->getUserGroupForm(
                ControllerMethodConstant::CREATE,
                $userGroupModel
            )
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = CreateUserGroupCommand::create($userGroupModel);
            $this->commandBus->dispatch($command);

            return $this->requestService->redirectToRoute('user_group_list');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->userGroupFormFactory->getUserGroupForm(
                ControllerMethodConstant::CREATE_AJAX,
                $userGroupModel
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/user_group/user_group.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.user_group_create'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
