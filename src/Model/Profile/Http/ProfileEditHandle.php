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

namespace App\Model\Profile\Http;

use App\Constant\FlashTypeConstant;
use App\Model\Profile\Form\ProfileFormType;
use App\Model\User\Factory\UserFactory;
use App\Model\User\UserModel;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ProfileEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private UserFactory $userFactory,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        $userModel = UserModel::fromUser($user);
        $form = $this->formFactory
            ->create(ProfileFormType::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $refreshPage = $userModel->locale !== null && $userModel->locale !== $user->getLocale();

                $this->userFactory->flushFromModel($userModel, $user);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                if ($refreshPage) {
                    return $this->requestService->redirectToRoute('profile_edit', [
                        '_locale' => $user->getLocale()
                    ]);
                }
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
            }
        }

        return $this->twigRenderService->render('profile/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
