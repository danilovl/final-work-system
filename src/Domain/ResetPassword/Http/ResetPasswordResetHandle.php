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

namespace App\Domain\ResetPassword\Http;

use App\Application\Constant\FlashTypeConstant;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\ResetPassword\Bus\Command\ResetPassword\ResetPasswordCommand;
use App\Domain\ResetPassword\Exception\ResetPasswordExceptionInterface;
use App\Domain\ResetPassword\Form\ResetChangePasswordForm;
use App\Domain\ResetPassword\Service\ResetPasswordService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class ResetPasswordResetHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private FormFactoryInterface $formFactory,
        private ResetPasswordService $resetPasswordService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $token = $request->query->get('token');
        if ($token === null) {
            throw new NotFoundHttpException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordService->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->requestService->addFlash(
                FlashTypeConstant::ERROR->value,
                $this->translatorService->trans($e->getReason())
            );

            return $this->requestService->redirectToRoute('security_login');
        }

        $form = $this->formFactory
            ->create(ResetChangePasswordForm::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $command = ResetPasswordCommand::create($user, $plainPassword, $token);
            $this->commandBus->dispatchResult($command);

            $this->cleanSessionAfterReset();

            return $this->requestService->redirectToRoute('homepage');
        }

        return $this->twigRenderService->renderToResponse('domain/reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function cleanSessionAfterReset(): void
    {
        $this->requestService->getSession()->remove('reset_password_check_email');
        $this->requestService->getSession()->remove('reset_password_public_token');
    }
}
