<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Model\Version\Form\Factory;

use App\Constant\ControllerMethodConstant;
use App\Model\Media\Entity\Media;
use App\Model\Media\Facade\MediaMimeTypeFacade;
use App\Model\Version\Form\VersionForm;
use App\Model\Work\Entity\Work;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use App\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use App\Model\Media\MediaModel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;

class VersionFormFactory
{
    public function __construct(
        private RouterInterface $router,
        private MediaMimeTypeFacade $mediaMimeTypeFacade,
        private HashidsServiceInterface $hashidsService,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function getVersionForm(
        string $type,
        MediaModel $mediaModel,
        ?Media $media = null,
        Work $work = null
    ): FormInterface {
        $mimeTypes = $this->mediaMimeTypeFacade
            ->getFormValidationMimeTypes(true);

        $parameters = [
            'mimeTypes' => $mimeTypes
        ];

        switch ($type) {
            case ControllerMethodConstant::CREATE:
                $parameters['uploadMedia'] = true;

                break;
            case ControllerMethodConstant::EDIT:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work must not be null for edit ajax');
                }

                $parameters = array_merge($parameters, [
                    'action' => $this->router->generate('version_create_ajax', [
                        'id' => $this->hashidsService->encode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST,
                    'uploadMedia' => true
                ]);

                break;
            case ControllerMethodConstant::EDIT_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work must not be null for edit ajax');
                }

                $parameters = array_merge($parameters, [
                    'action' => $this->router->generate('version_edit_ajax', [
                        'id_work' => $this->hashidsService->encode($work->getId()),
                        'id_media' => $this->hashidsService->encode($media->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ]);

                break;
            default:
                throw new ConstantNotFoundException('Controller method type not found');
        }

        return $this->formFactory->create(VersionForm::class, $mediaModel, $parameters);
    }
}
