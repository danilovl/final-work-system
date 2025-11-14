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
    ControllerMethodConstant,
    FlashTypeConstant
};
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\Bus\Command\CreateUser\CreateUserCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Form\Factory\UserFormFactory;
use App\Domain\User\Model\UserModel;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class UserCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private UserFormFactory $userFormFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $userModel = new UserModel;

        $form = $this->userFormFactory
            ->getUserForm(ControllerMethodConstant::CREATE, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $command = CreateUserCommand::create($userModel);
                /** @var User|null $user */
                $user = $this->commandBus->dispatchResult($command);

                if ($user === null) {
                    $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.user.create.error');
                    $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.user.create.warning');
                } else {
                    $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.user.create.success');
                }
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
            }
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->userFormFactory->getUserForm(
                ControllerMethodConstant::CREATE_AJAX,
                $userModel
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/user/user.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'reload' => true,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.user_create'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
