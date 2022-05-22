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

namespace App\Domain\User\Http;

use App\Application\Constant\{
    FlashTypeConstant,
    ControllerMethodConstant
};
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\EventDispatcher\UserEventDispatcherService;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Form\Factory\UserFormFactory;
use App\Domain\User\UserModel;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserCreateHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly TwigRenderService $twigRenderService,
        private readonly TranslatorService $translatorService,
        private readonly UserFacade $userFacade,
        private readonly UserFormFactory $userFormFactory,
        private readonly UserFactory $userFactory,
        private readonly UserEventDispatcherService $userEventDispatcherService
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
