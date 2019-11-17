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

namespace FinalWork\FinalWorkBundle\Tests\Form\Constraint;

use FinalWork\FinalWorkBundle\Form\Constraint\{
    ConversationMessageName,
    ConversationMessageNameValidator
};
use FinalWork\FinalWorkBundle\Model\ConversationMessage\ConversationComposeMessageModel;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\{
    FormInterface,
    FormTypeInterface
};
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ConversationMessageNameValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @return ConversationMessageNameValidator
     */
    protected function createValidator(): ConversationMessageNameValidator
    {
        return new ConversationMessageNameValidator;
    }

    /**
     * @param array $data
     * @return MockObject|FormTypeInterface
     */
    private function getFormMock(array $data): MockObject
    {
        $conversationComposeMessageModel = new ConversationComposeMessageModel;
        $conversationComposeMessageModel->conversation = $data['conversation'];

        $mockObject = $this->createMock(FormInterface::class);
        $mockObject->expects($this->any())
            ->method('getData')
            ->willReturn($conversationComposeMessageModel);

        return $mockObject;
    }

    /**
     * @dataProvider validProvider
     * @param string|null $value
     * @param array $data
     */
    public function testIsValid(?string $value, array $data): void
    {
        $this->setRoot($this->getFormMock($data));

        $this->validator->initialize($this->context);
        $this->validator->validate($value, new ConversationMessageName);

        $this->assertNoViolation();
    }

    /**
     * @dataProvider notValidProvider
     * @param string|null $value
     * @param array $data
     */
    public function testIsNotValid(?string $value, array $data): void
    {
        $this->setRoot($this->getFormMock($data));

        $this->validator->initialize($this->context);
        $this->validator->validate($value, new ConversationMessageName);

        $this->buildViolation('This value should not be blank.')->assertRaised();
    }

    /**
     * @return Generator
     */
    public function validProvider(): Generator
    {
        yield ['name', ['conversation' => [1]]];
        yield ['name', ['conversation' => [1, 2]]];
    }

    /**
     * @return Generator
     */
    public function notValidProvider(): Generator
    {
        yield ['', ['conversation' => [1, 2]]];
        yield [null, ['conversation' => [1, 2, 3]]];
    }
}