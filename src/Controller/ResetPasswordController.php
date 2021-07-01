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

namespace App\Controller;

use App\Constant\FlashTypeConstant;
use App\Exception\ResetPasswordExceptionInterface;
use App\Form\{
    ResetChangePasswordForm,
    ResetPasswordRequestForm
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ResetPasswordController extends BaseController
{
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestForm::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail($form->get('email')->getData());
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function checkEmail(): Response
    {
        if (!$this->getSession()->get('reset_password_check_email')) {
            return $this->redirectToRoute('reset_password_forgot_request');
        }

        return $this->render('reset_password/check_email.html.twig', [
            'tokenLifetime' => $this->get('app.reset_password')->getTokenLifetime(),
        ]);
    }

    public function reset(Request $request): Response
    {
        $token = $request->get('token');
        if ($token === null) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->get('app.reset_password')->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('error', $this->trans($e->getReason()));

            return $this->redirectToRoute('security_login');
        }

        $form = $this->createForm(ResetChangePasswordForm::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.reset_password')->removeResetRequest($token);

            $encodedPassword = $this->get('app.user_password_encoder')->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->flushEntity($user);

            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $email): RedirectResponse
    {
        $user = $this->get('app.facade.user')->findOneByEmail($email, true);

        $this->getSession()->set('reset_password_check_email', true);

        if (!$user) {
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'Bad email');

            return $this->redirectToRoute('reset_password_forgot_request');
        }

        try {
            $resetToken = $this->get('app.reset_password')->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlashTrans(FlashTypeConstant::ERROR, $this->trans($e->getReason()));

            return $this->redirectToRoute('reset_password_forgot_request');
        }

        $this->get('app.event_dispatcher.security')->onResetPasswordTokenCreate(
            $resetToken,
            $this->get('app.reset_password')->getTokenLifetime()
        );

        return $this->redirectToRoute('reset_password_check_email');
    }

    private function cleanSessionAfterReset(): void
    {
        $this->getSession()->remove('reset_password_check_email');
        $this->getSession()->remove('reset_password_public_token');
    }

    private function getSession(): SessionInterface
    {
        return $this->get('request_stack')->getCurrentRequest()->getSession();
    }
}
