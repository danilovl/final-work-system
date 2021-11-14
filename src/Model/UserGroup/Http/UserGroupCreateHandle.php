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

class UserGroupCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private UserGroupFactory $userGroupFactory,
        private UserGroupFormFactory $userGroupFormFactory
    ) {
    }

    public function handle(Request $request): Response
    {
        $userGroupModel = new UserGroupModel;
        $userGroupModel->name = 'Name';

        $form = $this->userGroupFormFactory
            ->getUserGroupForm(
                ControllerMethodConstant::CREATE,
                $userGroupModel
            )
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->userGroupFactory->flushFromModel($userGroupModel);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('user_group_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->userGroupFormFactory->getUserGroupForm(
                ControllerMethodConstant::CREATE_AJAX,
                $userGroupModel
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'user_group/user_group.html.twig');

        return $this->twigRenderService->render($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.user_group_create'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
