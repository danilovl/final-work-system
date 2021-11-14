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

namespace App\Model\User\Http;

use App\Model\User\EventDispatcher\UserEventDispatcherService;
use App\Model\User\Facade\UserFacade;
use App\Model\User\Factory\UserFactory;
use App\Model\User\Form\Factory\UserFormFactory;
use App\Model\User\UserModel;
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

class UserCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private UserFacade $userFacade,
        private UserFormFactory $userFormFactory,
        private UserFactory $userFactory,
        private UserEventDispatcherService $userEventDispatcherService
    ) {
    }

    public function handle(Request $request): Response
    {
        $userFacade = $this->userFacade;
        $userModel = new UserModel;

        $form = $this->userFormFactory
            ->getUserForm(ControllerMethodConstant::CREATE, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $email = $userModel->email;
                $username = $userModel->username;

                if ($userFacade->findOneByUsername($username) || $userFacade->findOneByEmail($email)) {
                    $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.user.create.error');
                    $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.user.create.warning');
                } else {
                    $newUser = $this->userFactory->createNewUser($userModel);
                    $this->userEventDispatcherService->onUserCreate($newUser);

                    $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.user.create.success');
                }
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
            }
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->userFormFactory->getUserForm(
                ControllerMethodConstant::CREATE_AJAX,
                $userModel
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'user/user.html.twig');

        return $this->twigRenderService->render($template, [
            'reload' => true,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.user_create'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
