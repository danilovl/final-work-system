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

use DateTime;
use Exception;
use FinalWork\FinalWorkBundle\Entity\Event;
use FinalWork\FinalWorkBundle\Form\CommentForm;
use FinalWork\FinalWorkBundle\Model\Comment\CommentModel;
use FinalWork\FinalWorkBundle\Tests\Form\Traits\ExtensionsTrait;
use FinalWork\SonataUserBundle\Entity\User;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Test\TypeTestCase;

class CommentFormTest extends TypeTestCase
{
    use ExtensionsTrait;

    /**
     * @param int $id
     * @return MockObject|User
     */
    private function getUserMock(int $id): MockObject
    {
        $user = $this->createMock(User::class);
        $user->method('getId')
            ->willReturn($id);

        return $user;
    }

    /**
     * @param bool $isOwner
     * @param string $dateTime
     * @return MockObject|Event
     * @throws Exception
     */
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
     * @param bool $isOwner
     * @param int $userId
     * @param string $dateTime
     * @param bool $isContentShow
     * @throws Exception
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

        $this->assertEquals($isContentFieldExist, $isContentShow);
        $this->assertTrue($form->isSynchronized());
    }

    /**
     * @return Generator
     */
    public function commentProvider(): Generator
    {
        yield [true, 1, '2016-04-06 10:00:00', true];
        yield [false, 2, '2016-04-06 10:00:00', false];
        yield [false, 2, '2099-04-06 10:00:00', true];
    }
}
