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

namespace App\Domain\Profile\Http;

use App\Application\Constant\FlashTypeConstant;
use App\Application\Service\{
    UserService,
    RequestService,
    TwigRenderService
};
use App\Domain\Profile\Form\ProfileFormType;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\UserModel;
use Symfony\Component\Form\FormFactoryInterface;
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
