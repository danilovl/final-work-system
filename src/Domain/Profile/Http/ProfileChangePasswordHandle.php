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
    PasswordUpdater,
    TwigRenderService
};
use App\Domain\ResetPassword\Form\ProfileChangePasswordFormType;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\UserModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class ProfileChangePasswordHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private PasswordUpdater $passwordUpdater,
        private UserFactory $userFactory
    ) {}

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $userModel = UserModel::fromUser($user);
        $form = $this->formFactory
            ->create(ProfileChangePasswordFormType::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->passwordUpdater->hashPassword(
                    $form->get('plainPassword')->getData(),
                    $user,
                    $userModel
                );

                $this->userFactory->flushFromModel($userModel, $user);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
            }
        }

        return $this->twigRenderService->render('profile/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
