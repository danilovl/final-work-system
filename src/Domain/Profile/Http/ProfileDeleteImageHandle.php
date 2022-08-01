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
use App\Application\Service\{
    UserService,
    RequestService,
    EntityManagerService
};
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProfileDeleteImageHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly EntityManagerService $entityManagerService
    ) {}

    public function handle(): RedirectResponse
    {
        $user = $this->userService->getUser();

        try {
            $this->entityManagerService->remove($user->getProfileImage());
            $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.delete.success');
        } catch (Exception) {
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.delete.error');
            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.delete.error');
        }

        return $this->requestService->redirectToRoute('profile_show');
    }
}
