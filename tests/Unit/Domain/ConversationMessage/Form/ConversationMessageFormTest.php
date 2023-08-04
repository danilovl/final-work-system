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

namespace App\Tests\Unit\Domain\ConversationMessage\Form;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Form\ConversationMessageForm;
use App\Domain\ConversationMessage\Model\ConversationMessageModel;
use App\Domain\User\Entity\User;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

class ConversationMessageFormTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    private function getUserMock(): MockObject
    {
        $user = $this->createMock(User::class);
        $user->expects($this->any())
            ->method('getMessageHeaderFooter')
            ->willReturn('');

        return $user;
    }

    #[DataProvider('messageProvider')]
    public function testSubmitValidData(
        array $data,
        bool $isValid
    ): void {
        $conversationMessageModel = new ConversationMessageModel;
        $conversationMessageModel->conversation = $this->createMock(Conversation::class);
        $conversationMessageModel->owner = $this->createMock(User::class);

        $form = $this->factory->create(ConversationMessageForm::class, $conversationMessageModel, [
            'user' => $this->getUserMock()
        ]);

        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($isValid, $form->isValid());
    }

    public static function messageProvider(): Generator
    {
        yield [['content' => 'text'], true];
        yield [['content' => null], true];
    }
}
