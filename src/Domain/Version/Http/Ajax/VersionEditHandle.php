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

use App\Infrastructure\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, QueryBusInterface};
use App\Domain\MediaMimeType\Bus\Query\MediaMimeTypeList\{GetMediaMimeTypeListQuery, GetMediaMimeTypeListQueryResult};
use App\Infrastructure\Service\RequestService;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Model\MediaModel;
use App\Domain\User\Service\UserService;
use App\Domain\Version\Bus\Command\EditVersion\EditVersionCommand;
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
        private QueryBusInterface $queryBus,
        private FormFactoryInterface $formFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Media $media): JsonResponse
    {
        $mediaModel = MediaModel::fromMedia($media);
        $getMediaMimeTypeListQuery = GetMediaMimeTypeListQuery::create(true);
        /** @var GetMediaMimeTypeListQueryResult $getMediaMimeTypeListQueryResult */
        $getMediaMimeTypeListQueryResult = $this->queryBus->handle($getMediaMimeTypeListQuery);
        $mimeTypes = $getMediaMimeTypeListQueryResult->mediaMimeTypes;

        $form = $this->formFactory
            ->create(VersionForm::class, $mediaModel, [
                'mimeTypes' => $mimeTypes
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userService->getUser();

            $command = EditVersionCommand::create($media, $mediaModel, $user);
            $this->commandBus->dispatch($command);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
