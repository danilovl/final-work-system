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

namespace App\Tests\Unit\Domain\EventScheduleTemplate\Validation\Constraint;

use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventScheduleTemplate\Model\EventScheduleTemplateModel;
use App\Domain\EventScheduleTemplate\Validation\Constraint\{
    EventScheduleTemplateTime,
    EventScheduleTemplateTimeValidator
};
use App\Domain\EventType\Constant\EventTypeConstant;
use App\Domain\EventType\Entity\EventType;
use DateTime;
use stdClass;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class EventScheduleTemplateTimeValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): EventScheduleTemplateTimeValidator
    {
        return new EventScheduleTemplateTimeValidator;
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new EventScheduleTemplateTime);

        $this->assertNoViolation();
    }

    public function testRejectsEntityInsteadOfModel(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(new stdClass, new EventScheduleTemplateTime);
    }

    public function testIncompleteModelIsLeftToNotBlank(): void
    {
        $model = $this->createModel(EventTypeConstant::PERSONAL, '09:00', '10:00', name: 'Name');
        $model->start = null;

        $this->validator->validate($model, new EventScheduleTemplateTime);

        $this->assertNoViolation();
    }

    public function testValidPersonalTemplate(): void
    {
        $model = $this->createModel(EventTypeConstant::PERSONAL, '09:00', '10:00', name: 'Name');

        $this->validator->validate($model, new EventScheduleTemplateTime);

        $this->assertNoViolation();
    }

    public function testValidConsultationTemplate(): void
    {
        $model = $this->createModel(EventTypeConstant::CONSULTATION, '09:00', '10:00', address: new EventAddress);

        $this->validator->validate($model, new EventScheduleTemplateTime);

        $this->assertNoViolation();
    }

    public function testStartAfterEnd(): void
    {
        $model = $this->createModel(EventTypeConstant::PERSONAL, '11:00', '10:00', name: 'Name');

        $this->validator->validate($model, new EventScheduleTemplateTime);

        $this->buildViolation('app.form.validation.date_start_more_end')
            ->atPath('property.path.start')
            ->buildNextViolation('This value should be greater than {{ compared_value }}.')
            ->setParameter('{{ compared_value }}', '11:00')
            ->atPath('property.path.start')
            ->assertRaised();
    }

    public function testStartEqualsEnd(): void
    {
        $model = $this->createModel(EventTypeConstant::PERSONAL, '09:00', '09:00', name: 'Name');

        $this->validator->validate($model, new EventScheduleTemplateTime);

        $this->buildViolation('This value should not be equal to {{ compared_value }}.')
            ->setParameter('{{ compared_value }}', '09:00')
            ->atPath('property.path.end')
            ->assertRaised();
    }

    public function testConsultationWithoutAddress(): void
    {
        $model = $this->createModel(EventTypeConstant::CONSULTATION, '09:00', '10:00');

        $this->validator->validate($model, new EventScheduleTemplateTime);

        $this->buildViolation('This value should not be blank.')
            ->atPath('property.path.address')
            ->assertRaised();
    }

    public function testPersonalWithoutName(): void
    {
        $model = $this->createModel(EventTypeConstant::PERSONAL, '09:00', '10:00');

        $this->validator->validate($model, new EventScheduleTemplateTime);

        $this->buildViolation('This value should not be blank.')
            ->atPath('property.path.name')
            ->assertRaised();
    }

    private function createModel(
        EventTypeConstant $type,
        string $start,
        string $end,
        ?string $name = null,
        ?EventAddress $address = null
    ): EventScheduleTemplateModel {
        $eventType = new EventType;
        $eventType->setId($type->value);

        $model = new EventScheduleTemplateModel;
        $model->type = $eventType;
        $model->day = 1;
        $model->name = $name;
        $model->address = $address;
        $model->start = new DateTime('1970-01-01 ' . $start);
        $model->end = new DateTime('1970-01-01 ' . $end);

        return $model;
    }
}
