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
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\ResetPassword\EventDispatcher\SecurityDispatcher;
use App\Domain\ResetPassword\Exception\ResetPasswordExceptionInterface;
use App\Domain\ResetPassword\Form\ResetPasswordRequestForm;
use App\Domain\ResetPassword\Service\ResetPasswordService;
use App\Domain\User\Facade\UserFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

readonly class ResetPasswordRequestHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private FormFactoryInterface $formFactory,
        private UserFacade $userFacade,
        private SecurityDispatcher $securityDispatcher,
        private ResetPasswordService $resetPasswordService
    ) {}

    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory
            ->create(ResetPasswordRequestForm::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            return $this->processSendingPasswordResetEmail($email);
        }

        return $this->twigRenderService->renderToResponse('domain/reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $email): RedirectResponse
    {
        $user = $this->userFacade->findOneByEmail($email, true);

        $this->requestService
            ->getSession()
            ->set('reset_password_check_email', true);

        if (!$user) {
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'Bad email');

            return $this->requestService->redirectToRoute('reset_password_forgot_request');
        }

        try {
            $resetToken = $this->resetPasswordService->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->requestService->addFlash(
                FlashTypeConstant::ERROR->value,
                $this->translatorService->trans($e->getReason())
            );

            return $this->requestService->redirectToRoute('reset_password_forgot_request');
        }

        $this->securityDispatcher->onResetPasswordTokenCreate(
            $resetToken,
            $this->resetPasswordService->getTokenLifetime()
        );

        return $this->requestService->redirectToRoute('reset_password_check_email');
    }
}
