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

namespace App\Tests\Form;

use App\Model\Event\Entity\Event;
use App\Model\User\Entity\User;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use DateTime;
use App\Model\Comment\Form\CommentForm;
use App\Model\Comment\CommentModel;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Test\TypeTestCase;

class CommentFormTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    private function getUserMock(int $id): MockObject
    {
        $user = $this->createMock(User::class);
        $user->method('getId')
            ->willReturn($id);

        return $user;
    }

    private function getEventMock(bool $isOwner, string $dateTime): MockObject
    {
        $event = $this->createMock(Event::class);
        $event->expects($this->any())
            ->method('getStart')
            ->willReturn(new DateTime($dateTime));

        $event->expects($this->any())
            ->method('isOwner')
            ->willReturn($isOwner);

        return $event;
    }

    /**
     * @dataProvider commentProvider
     */
    public function testSubmitValidData(
        bool $isOwner,
        int $userId,
        string $dateTime,
        bool $isContentShow
    ): void {
        $commentModel = new CommentModel;

        $form = $this->factory->create(CommentForm::class, $commentModel, [
            'user' => $this->getUserMock($userId),
            'event' => $this->getEventMock($isOwner, $dateTime)
        ]);

        $form->submit(['content' => 'text']);
        $isContentFieldExist = $form->has('content');

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($isContentShow, $isContentFieldExist);
    }

    public function commentProvider(): Generator
    {
        yield [true, 1, '2016-04-06 10:00:00', true];
        yield [false, 2, '2016-04-06 10:00:00', false];
        yield [false, 2, '2099-04-06 10:00:00', true];
    }
}
