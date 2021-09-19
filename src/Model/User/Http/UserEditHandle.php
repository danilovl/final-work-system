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

use App\Entity\User;
use App\EventDispatcher\UserEventDispatcherService;
use App\Form\Factory\UserFormFactory;
use App\Model\User\Factory\UserFactory;
use App\Model\User\UserModel;
use App\Constant\{
    SeoPageConstant,
    FlashTypeConstant,
    ControllerMethodConstant
};
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use App\Service\{
    UserService,
    RequestService,
    SeoPageService,
    TranslatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private UserService $userService,
        private HashidsServiceInterface $hashidsService,
        private UserFormFactory $userFormFactory,
        private UserFactory $userFactory,
        private UserEventDispatcherService $userEventDispatcherService,
        private SeoPageService $seoPageService
    ) {
    }

    public function handle(Request $request, User $user): Response
    {
        $userModel = UserModel::fromUser($user);

        $form = $this->userFormFactory
            ->getUserForm(ControllerMethodConstant::EDIT, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->userFactory
                    ->flushFromModel($userModel, $user);

                $this->userEventDispatcherService->onUserEdit(
                    $user,
                    $this->userService->getUser()
                );

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('user_edit', [
                    'id' => $this->hashidsService->encode($user->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->userFormFactory->getUserForm(
                ControllerMethodConstant::EDIT_AJAX,
                $userModel,
                $user
            );
        }

        $this->seoPageService->addTitle($user->getUsername(), SeoPageConstant::VERTICAL_SEPARATOR);

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'user/user.html.twig');

        return $this->twigRenderService->render($template, [
            'reload' => true,
            'user' => $user,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.user_edit'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
