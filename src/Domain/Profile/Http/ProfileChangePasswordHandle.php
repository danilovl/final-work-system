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
    RequestService,
    TwigRenderService
};
use App\Domain\ResetPassword\Form\ProfileChangePasswordFormType;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Model\UserModel;
use App\Domain\User\Service\PasswordUpdater;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

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

        $userModel = UserModel::fromUser($user);
        $form = $this->formFactory
            ->create(ProfileChangePasswordFormType::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var string $plainPassword */
                $plainPassword = $form->get('plainPassword')->getData();

                $this->passwordUpdater->hashPassword(
                    $plainPassword,
                    $user,
                    $userModel
                );

                $this->userFactory->flushFromModel($userModel, $user);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.save.warning');
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.save.error');
            }
        }

        return $this->twigRenderService->renderToResponse('domain/profile/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
