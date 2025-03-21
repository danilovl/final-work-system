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

namespace App\Domain\Version\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Application\Service\RequestService;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Facade\MediaMimeTypeFacade;
use App\Domain\Media\Model\MediaModel;
use App\Domain\User\Service\UserService;
use App\Domain\Version\Bus\Command\EditVersion\EditVersionCommand;
use App\Domain\Version\EventDispatcher\VersionEventDispatcherService;
use App\Domain\Version\Form\VersionForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class VersionEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private MediaMimeTypeFacade $mediaMimeTypeFacade,
        private FormFactoryInterface $formFactory,
        private VersionEventDispatcherService $versionEventDispatcherService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Media $media): JsonResponse
    {
        $mediaModel = MediaModel::fromMedia($media);
        $form = $this->formFactory
            ->create(VersionForm::class, $mediaModel, [
                'mimeTypes' => $this->mediaMimeTypeFacade->getFormValidationMimeTypes(true)
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $editVersionCommand = EditVersionCommand::create($media, $mediaModel);
            /** @var Media $media */
            $media = $this->commandBus->dispatchResult($editVersionCommand);

            $media->setOwner($this->userService->getUser());
            $this->versionEventDispatcherService->onVersionEdit($media);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
