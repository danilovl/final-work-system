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

use App\Infrastructure\Service\{
    RequestService,
    TwigRenderService
};
use App\Domain\ResetPassword\Service\ResetPasswordService;
use Symfony\Component\HttpFoundation\Response;

readonly class ResetPasswordCheckEmailHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private ResetPasswordService $resetPasswordService
    ) {}

    public function __invoke(): Response
    {
        if (!$this->requestService->getSession()->get('reset_password_check_email')) {
            return $this->requestService->redirectToRoute('reset_password_forgot_request');
        }

        return $this->twigRenderService->renderToResponse('domain/reset_password/check_email.html.twig', [
            'tokenLifetime' => $this->resetPasswordService->getTokenLifetime(),
        ]);
    }
}
