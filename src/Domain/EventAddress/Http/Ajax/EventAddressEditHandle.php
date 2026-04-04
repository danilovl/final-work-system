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

namespace App\Domain\EventAddress\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Infrastructure\Service\RequestService;
use App\Domain\EventAddress\Bus\Command\EditEventAddress\EditEventAddressCommand;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\Facade\EventAddressFacade;
use App\Domain\EventAddress\Form\EventAddressForm;
use App\Domain\EventAddress\Model\EventAddressModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};
use Symfony\Component\Messenger\MessageBusInterface;

readonly class EventAddressEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EventAddressFacade $eventAddressFacade,
        private FormFactoryInterface $formFactory,
        private MessageBusInterface $messageBus
    ) {}

    public function __invoke(Request $request, EventAddress $eventAddress): JsonResponse
    {
        $eventAddressModel = EventAddressModel::fromEventAddress($eventAddress);
        $form = $this->formFactory
            ->create(EventAddressForm::class, $eventAddressModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($eventAddress->isSkype()) {
                $eventAddressSkype = $this->eventAddressFacade->findSkypeByOwner(
                    $this->userService->getUser()
                );

                $eventAddressSkype?->setSkype(false);
            }

            $command = EditEventAddressCommand::create($eventAddressModel, $eventAddress);
            $this->messageBus->dispatch($command);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
