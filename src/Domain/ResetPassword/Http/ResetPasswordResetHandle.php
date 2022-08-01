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
use App\Application\Exception\ResetPasswordExceptionInterface;
use App\Application\Service\{
    RequestService,
    TwigRenderService,
    TranslatorService,
    EntityManagerService,
    ResetPasswordService
};
use App\Domain\ResetPassword\Form\ResetChangePasswordForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordResetHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly EntityManagerService $entityManagerService,
        private readonly TwigRenderService $twigRenderService,
        private readonly TranslatorService $translatorService,
        private readonly FormFactoryInterface $formFactory,
        private readonly ResetPasswordService $resetPasswordService,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    ) {}

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
