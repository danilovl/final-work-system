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

namespace App\Model\Profile\Http;

use App\Constant\FlashTypeConstant;
use App\Service\{
    UserService,
    EntityManagerService,
    RequestService
};
use Symfony\Component\HttpFoundation\RedirectResponse;
use Exception;

class ProfileDeleteImageHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService
    ) {
    }

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
