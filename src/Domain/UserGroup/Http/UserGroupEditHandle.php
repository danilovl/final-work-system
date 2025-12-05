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

use App\Application\Constant\{
    ControllerMethodConstant,
    FlashTypeConstant
};
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\UserGroup\Bus\Command\EditUserGroup\EditUserGroupCommand;
use App\Domain\UserGroup\Entity\Group;
use App\Domain\UserGroup\Form\Factory\UserGroupFormFactory;
use App\Domain\UserGroup\Model\UserGroupModel;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class UserGroupEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private UserGroupFormFactory $userGroupFormFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Group $group): Response
    {
        $userGroupModel = UserGroupModel::fromGroup($group);

        $form = $this->userGroupFormFactory
            ->getUserGroupForm(
                ControllerMethodConstant::EDIT,
                $userGroupModel
            )
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $command = EditUserGroupCommand::create($userGroupModel, $group);
                $this->commandBus->dispatchResult($command);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('user_group_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->userGroupFormFactory->getUserGroupForm(
                ControllerMethodConstant::EDIT_AJAX,
                $userGroupModel,
                $group
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/user_group/user_group.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'group' => $group,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.user_group_edit'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
