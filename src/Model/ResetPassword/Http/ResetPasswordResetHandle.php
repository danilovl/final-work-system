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

use App\Constant\FlashTypeConstant;
use App\Exception\ResetPasswordExceptionInterface;
use App\Model\ResetPassword\Form\ResetChangePasswordForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService,
    EntityManagerService,
    ResetPasswordService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ResetPasswordResetHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private FormFactoryInterface $formFactory,
        private ResetPasswordService $resetPasswordService,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function handle(Request $request): Response
    {
        $token = $request->query->get('token');
        if ($token === null) {
            throw new NotFoundHttpException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordService->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->requestService->addFlash(
                FlashTypeConstant::ERROR,
                $this->translatorService->trans($e->getReason())
            );

            return $this->requestService->redirectToRoute('security_login');
        }

        $form = $this->formFactory
            ->create(ResetChangePasswordForm::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordService->removeResetRequest($token);

            $encodedPassword = $this->userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->entityManagerService->flush($user);

            $this->cleanSessionAfterReset();

            return $this->requestService->redirectToRoute('homepage');
        }

        return $this->twigRenderService->render('reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function cleanSessionAfterReset(): void
    {
        $this->requestService->getSession()->remove('reset_password_check_email');
        $this->requestService->getSession()->remove('reset_password_public_token');
    }
}
