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

namespace App\Domain\EventAddress\Form\Factory;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Exception\ConstantNotFoundException;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\EventAddressModel;
use App\Domain\EventAddress\Form\EventAddressForm;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\{
    FormFactoryInterface,
    FormInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class EventAddressFormFactory
{
    public function __construct(
        private RouterInterface $router,
        private HashidsServiceInterface $hashidsService,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function getEventAddressForm(
        string $type,
        EventAddressModel $eventAddressModel,
        EventAddress $eventAddress = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->router->generate('event_address_create_ajax'),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $parameters = [
                    'action' => $this->router->generate('event_address_edit_ajax', [
                        'id' => $this->hashidsService->encode($eventAddress->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            default:
                throw new ConstantNotFoundException('Controller method type constant not found');
        }

        return $this->formFactory->create(EventAddressForm::class, $eventAddressModel, $parameters);
    }
}


