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

namespace App\Domain\EventCalendar\Request;

use App\Application\Constant\{
    FlashTypeConstant,
    DateFormatConstant
};
use App\Application\Helper\DateHelper;
use App\Application\Request\AbstractAjaxJsonRequest;
use App\Infrastructure\Service\TranslatorService;
use Symfony\Component\HttpFoundation\{
    Request,
    RequestStack
};
use Symfony\Component\Validator\Constraints\{
    NotBlank,
    Collection
};
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetEventRequest extends AbstractAjaxJsonRequest
{
    public string $start;

    public string $end;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        protected readonly TranslatorService $translator
    ) {
        parent::__construct($validator, $requestStack);
    }

    public function handle(Request $request): bool
    {
        if (DateHelper::validateDate(DateFormatConstant::DATE_TIME->value, $this->start) === false ||
            DateHelper::validateDate(DateFormatConstant::DATE_TIME->value, $this->end) === false
        ) {
            return false;
        }

        return $this->start < $this->end;
    }

    /**
     * @return array<string, string>
     */
    protected function getNotifyMessage(): array
    {
        return [
            FlashTypeConstant::ERROR->value => $this->translator->trans('app.flash.form.create.error'),
            FlashTypeConstant::WARNING->value => $this->translator->trans('app.flash.form.create.warning')
        ];
    }

    protected function getConstraints(): Collection
    {
        return new Collection([
            'start' => [new NotBlank],
            'end' => [new NotBlank]
        ]);
    }
}
