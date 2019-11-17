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

namespace FinalWork\FinalWorkBundle\Form\Constraint;

use DateTime;
use Exception;
use FinalWork\FinalWorkBundle\Helper\DateHelper;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\{
    Constraint,
    ConstraintValidator
};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FirstWeekDayValidator extends ConstraintValidator
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     * @param DateTime $startDate
     * @param FirstWeekDay $constraint
     *
     * @throws Exception
     */
    public function validate($startDate, Constraint $constraint): void
    {
        if ($startDate === null) {
            return;
        }

        if (!$startDate instanceof DateTime) {
            throw new UnexpectedTypeException($startDate, DateTime::class);
        }

        $startWeekTest = DateHelper::actualWeekStartByDate(clone $startDate);
        if ($startDate->format('Y-m-d') !== $startWeekTest->format('Y-m-d')) {
            $this->context
                ->buildViolation($this->translator->trans($constraint->message))
                ->setTranslationDomain('messages')
                ->atPath('start')
                ->addViolation();
        }
    }
}