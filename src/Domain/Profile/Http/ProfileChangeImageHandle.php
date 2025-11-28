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
use App\Application\Service\{
    RequestService,
    TwigRenderService
};
use App\Domain\Media\Model\MediaModel;
use App\Domain\Profile\Bus\Command\ProfileChangeImage\ProfileChangeImageCommand;
use App\Domain\Profile\Form\ProfileMediaForm;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ProfileChangeImageHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private FormFactoryInterface $formFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $mediaModel = new MediaModel;
        $form = $this->formFactory
            ->create(ProfileMediaForm::class, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $command = ProfileChangeImageCommand::create($mediaModel, $user);
                $this->commandBus->dispatch($command);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
            }
        }

        return $this->twigRenderService->renderToResponse('domain/profile/edit_image.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
