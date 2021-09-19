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

namespace App\Model\ResetPassword\Http;

use App\EventDispatcher\SecurityDispatcherService;
use App\Exception\ResetPasswordExceptionInterface;
use App\Form\ResetPasswordRequestForm;
use App\Model\User\Facade\UserFacade;
use App\Constant\FlashTypeConstant;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService,
    ResetPasswordService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class ResetPasswordRequestHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private FormFactoryInterface $formFactory,
        private UserFacade $userFacade,
        private SecurityDispatcherService $securityDispatcherService,
        private ResetPasswordService $resetPasswordService
    ) {
    }

    public function handle(Request $request): Response
    {
        $form = $this->formFactory
            ->create(ResetPasswordRequestForm::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData()
            );
        }

        return $this->twigRenderService->render('reset_password/request.html.twig', [
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
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'Bad email');

            return $this->requestService->redirectToRoute('reset_password_forgot_request');
        }

        try {
            $resetToken = $this->resetPasswordService->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->requestService->addFlash(
                FlashTypeConstant::ERROR,
                $this->translatorService->trans($e->getReason())
            );

            return $this->requestService->redirectToRoute('reset_password_forgot_request');
        }

        $this->securityDispatcherService->onResetPasswordTokenCreate(
            $resetToken,
            $this->resetPasswordService->getTokenLifetime()
        );

        return $this->requestService->redirectToRoute('reset_password_check_email');
    }
}