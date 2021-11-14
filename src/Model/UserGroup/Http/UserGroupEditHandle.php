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

namespace App\Model\UserGroup\Http;

use App\Entity\Group;
use App\Model\UserGroup\Factory\UserGroupFactory;
use App\Model\UserGroup\Form\Factory\UserGroupFormFactory;
use App\Model\UserGroup\UserGroupModel;
use App\Constant\{
    FlashTypeConstant,
    ControllerMethodConstant
};
use App\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserGroupEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private UserGroupFactory $userGroupFactory,
        private UserGroupFormFactory $userGroupFormFactory
    ) {
    }

    public function handle(Request $request, Group $group): Response
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
                $this->userGroupFactory->flushFromModel($userGroupModel, $group);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('user_group_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->userGroupFormFactory->getUserGroupForm(
                ControllerMethodConstant::EDIT_AJAX,
                $userGroupModel,
                $group
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'user_group/user_group.html.twig');

        return $this->twigRenderService->render($template, [
            'group' => $group,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.user_group_edit'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
