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

namespace App\Tests\Unit\Domain\ConversationMessage\Validation\Constraint;

use App\Domain\ConversationMessage\Model\ConversationComposeMessageModel;
use App\Domain\ConversationMessage\Validation\Constraint\{
    ConversationMessageName,
    ConversationMessageNameValidator
};
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ConversationMessageNameValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ConversationMessageNameValidator
    {
        return new ConversationMessageNameValidator;
    }

    private function getFormMock(array $data): FormInterface
    {
        $conversationComposeMessageModel = new ConversationComposeMessageModel;
        $conversationComposeMessageModel->conversation = $data['conversation'];

        $mockObject = $this->createStub(FormInterface::class);
        $mockObject->method('getData')
            ->willReturn($conversationComposeMessageModel);

        return $mockObject;
    }

    #[DataProvider('provideIsValidCases')]
    public function testIsValid(?string $value, array $data): void
    {
        $this->setRoot($this->getFormMock($data));

        $this->validator->initialize($this->context);
        $this->validator->validate($value, new ConversationMessageName);

        $this->assertNoViolation();
    }

    #[DataProvider('provideIsNotValidCases')]
    public function testIsNotValid(?string $value, array $data): void
    {
        $this->setRoot($this->getFormMock($data));

        $this->validator->initialize($this->context);
        $this->validator->validate($value, new ConversationMessageName);

        $this->buildViolation('This value should not be blank.')->assertRaised();
    }

    public static function provideIsValidCases(): Generator
    {
        yield ['name', ['conversation' => [1]]];
        yield ['name', ['conversation' => [1, 2]]];
    }

    public static function provideIsNotValidCases(): Generator
    {
        yield ['', ['conversation' => [1, 2]]];
        yield [null, ['conversation' => [1, 2, 3]]];
    }
}
