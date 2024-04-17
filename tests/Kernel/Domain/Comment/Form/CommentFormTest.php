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

namespace App\Tests\Kernel\Domain\Comment\Form;

use App\Domain\Comment\Form\CommentForm;
use App\Domain\Comment\Model\CommentModel;
use App\Domain\Event\Entity\Event;
use App\Domain\User\Entity\User;
use DateTime;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactory;

class CommentFormTest extends KernelTestCase
{
    private FormFactory $formFactory;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->formFactory = $kernel->getContainer()->get('form.factory');
    }

    private function getUserMock(int $id): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')
            ->willReturn($id);

        return $user;
    }

    private function getEventMock(bool $isOwner, string $dateTime): Event
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

    #[DataProvider('commentProvider')]
    public function testSubmitValidData(
        bool $isOwner,
        int $userId,
        string $dateTime,
        array $submitData,
        bool $isContentShow
    ): void {
        $commentModel = new CommentModel(
            $this->getUserMock($userId),
            $this->getEventMock($isOwner, $dateTime)
        );

        $form = $this->formFactory->create(CommentForm::class, $commentModel, [
            'user' => $this->getUserMock($userId),
            'event' => $this->getEventMock($isOwner, $dateTime),
            'csrf_protection' => false
        ]);

        $form->submit($submitData);
        $isContentFieldExist = $form->has('content');

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $this->assertEquals($isContentShow, $isContentFieldExist);
    }

    public static function commentProvider(): Generator
    {
        yield [true, 1, '2016-04-06 10:00:00', ['content' => 'text'], true];
        yield [false, 2, '2016-04-06 10:00:00', [], false];
        yield [false, 2, '2099-04-06 10:00:00', ['content' => 'text'], true];
    }
}
