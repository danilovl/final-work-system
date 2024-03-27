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

namespace App\Domain\Version\Form\Factory;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Exception\{
    ConstantNotFoundException};
use App\Application\Exception\RuntimeException;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Facade\MediaMimeTypeFacade;
use App\Domain\Media\Model\MediaModel;
use App\Domain\Version\Form\VersionForm;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\{
    FormFactoryInterface,
    FormInterface};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class VersionFormFactory
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly MediaMimeTypeFacade $mediaMimeTypeFacade,
        private readonly HashidsServiceInterface $hashidsService,
        private readonly FormFactoryInterface $formFactory
    ) {}

    public function getVersionForm(
        ControllerMethodConstant $type,
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
                if ($work === null || $media === null) {
                    throw new RuntimeException('Work and media must not be null for edit ajax');
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
