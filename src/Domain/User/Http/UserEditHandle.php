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
    SeoPageConstant
};
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\{
    RequestService,
    SeoPageService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\Bus\Command\EditUser\EditUserCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Form\Factory\UserFormFactory;
use App\Domain\User\Model\UserModel;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class UserEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private HashidsServiceInterface $hashidsService,
        private UserFormFactory $userFormFactory,
        private SeoPageService $seoPageService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, User $user): Response
    {
        $userModel = UserModel::fromUser($user);

        $form = $this->userFormFactory
            ->getUserForm(ControllerMethodConstant::EDIT, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = EditUserCommand::create($userModel, $user);
            $this->commandBus->dispatch($command);

            return $this->requestService->redirectToRoute('user_edit', [
                'id' => $this->hashidsService->encode($user->getId())
            ]);
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->userFormFactory->getUserForm(
                ControllerMethodConstant::EDIT_AJAX,
                $userModel,
                $user
            );
        }

        $this->seoPageService->addTitle($user->getUsername(), SeoPageConstant::VERTICAL_SEPARATOR->value);

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/user/user.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'reload' => true,
            'user' => $user,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.user_edit'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
