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
    ControllerMethodConstant,
    SeoPageConstant
};
use App\Application\Service\{
    UserService,
    RequestService,
    SeoPageService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\Entity\User;
use App\Domain\User\EventDispatcher\UserEventDispatcherService;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Form\Factory\UserFormFactory;
use App\Domain\User\UserModel;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserEditHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly TwigRenderService $twigRenderService,
        private readonly TranslatorService $translatorService,
        private readonly UserService $userService,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly UserFormFactory $userFormFactory,
        private readonly UserFactory $userFactory,
        private readonly UserEventDispatcherService $userEventDispatcherService,
        private readonly SeoPageService $seoPageService
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
