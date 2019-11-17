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

namespace FinalWork\FinalWorkBundle\Tests\Form;

use FinalWork\FinalWorkBundle\Entity\Conversation;
use FinalWork\FinalWorkBundle\Form\ConversationMessageForm;
use FinalWork\FinalWorkBundle\Model\ConversationMessage\ConversationMessageModel;
use FinalWork\FinalWorkBundle\Tests\Form\Traits\ExtensionsTrait;
use FinalWork\SonataUserBundle\Entity\User;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Test\TypeTestCase;

class ConversationMessageFormTest extends TypeTestCase
{
    use ExtensionsTrait;

    /**
     * @return MockObject|User
     */
    private function getUserMock(): MockObject
    {
        $user = $this->createMock(User::class);
        $user->expects($this->any())
            ->method('getMessageHeaderFooter')
            ->willReturn('');

        return $user;
    }

    /**
     * @dataProvider messageProvider
     * @param array $data
     * @param bool $isValid
     */
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
        $this->assertEquals($form->isValid(), $isValid);
    }

    /**
     * @return Generator
     */
    public function messageProvider(): Generator
    {
        yield [['content' => 'text'], true];
        yield [['content' => null], false];
    }
}
