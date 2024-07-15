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

namespace App\Application\Middleware\Event\Ajax;

use App\Application\Constant\{
    FlashTypeConstant,
    DateFormatConstant
};
use App\Application\Helper\DateHelper;
use App\Application\Service\TranslatorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class GetEventMiddleware
{
    public function __construct(readonly private TranslatorService $translator) {}

    public function __invoke(ControllerEvent $event): bool
    {
        $request = $event->getRequest();
        $startDate = $request->request->getString('start');
        $endDate = $request->request->getString('end');

        if (DateHelper::validateDate(DateFormatConstant::DATE_TIME->value, $startDate) === false ||
            DateHelper::validateDate(DateFormatConstant::DATE_TIME->value, $endDate) === false
        ) {
            $this->setResponse($event);

            return true;
        }

        if ($startDate > $endDate) {
            $this->setResponse($event);

            return true;
        }

        return true;
    }

    protected function setResponse(ControllerEvent $event): void
    {
        $event->setController(function (): JsonResponse {
            return new JsonResponse([
                'valid' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR->value => $this->translator->trans('app.flash.form.create.error'),
                    FlashTypeConstant::WARNING->value => $this->translator->trans('app.flash.form.create.warning')
                ]
            ]);
        });
    }
}
