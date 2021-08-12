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

namespace App\Form\Factory;

use App\Constant\ControllerMethodConstant;
use App\Entity\EventAddress;
use App\Exception\ConstantNotFoundException;
use App\Model\EventAddress\EventAddressModel;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Form\EventAddressForm;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;

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


