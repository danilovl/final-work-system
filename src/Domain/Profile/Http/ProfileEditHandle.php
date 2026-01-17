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
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\{
    RequestService,
    TwigRenderService
};
use App\Domain\Profile\Bus\Command\EditProfile\EditProfileCommand;
use App\Domain\Profile\Form\ProfileFormType;
use App\Domain\User\Model\UserModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ProfileEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private FormFactoryInterface $formFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $userModel = UserModel::fromUser($user);
        $form = $this->formFactory
            ->create(ProfileFormType::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $refreshPage = $userModel->locale !== null && $userModel->locale !== $user->getLocale();

                $command = EditProfileCommand::create($userModel, $user);
                $this->commandBus->dispatch($command);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.save.success');

                if ($refreshPage) {
                    return $this->requestService->redirectToRoute('profile_edit', [
                        '_locale' => $user->getLocale()
                    ]);
                }
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.save.warning');
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.save.error');
            }
        }

        return $this->twigRenderService->renderToResponse('domain/profile/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
