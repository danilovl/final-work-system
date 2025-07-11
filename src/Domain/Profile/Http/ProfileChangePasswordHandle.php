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

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\TwigRenderService;
use App\Domain\Profile\Bus\Command\ProfileChangePassword\ProfileChangePasswordCommand;
use App\Domain\ResetPassword\Form\ProfileChangePasswordFormType;
use App\Domain\User\Model\UserModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ProfileChangePasswordHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $userModel = UserModel::fromUser($user);
        $form = $this->formFactory
            ->create(ProfileChangePasswordFormType::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $command = ProfileChangePasswordCommand::create($user, $plainPassword);
            $this->commandBus->dispatch($command);
        }

        return $this->twigRenderService->renderToResponse('domain/profile/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
