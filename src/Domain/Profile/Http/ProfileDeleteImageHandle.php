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
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\RequestService;
use App\Infrastructure\Web\Form\Factory\FormActionFactory;
use App\Domain\Profile\Bus\Command\ProfileDeleteImage\ProfileDeleteImageCommand;
use App\Domain\User\Service\UserService;
use Exception;
use Symfony\Component\HttpFoundation\{
    Request,
    RedirectResponse
};
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class ProfileDeleteImageHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private CommandBusInterface $commandBus,
        private FormActionFactory $formActionFactory
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $isValidActionForm = $this->formActionFactory->isSubmittedAndValid($request);
        if (!$isValidActionForm) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $user = $this->userService->getUser();

        try {
            $command = ProfileDeleteImageCommand::create($user);
            $this->commandBus->dispatch($command);
        } catch (Exception) {
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.delete.error');
            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.delete.error');
        }

        return $this->requestService->redirectToRoute('profile_show');
    }
}
