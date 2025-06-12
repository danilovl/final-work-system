<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Tests\Kernel\Domain\User\Form\Type;

use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\Form\Type\UserRoleType;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;

class UserRoleTypeTest extends KernelTestCase
{
    private FormInterface $form;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $formFactory = $kernel->getContainer()->get('form.factory');

        $this->form = $formFactory->createBuilder(options: ['csrf_protection' => false])
            ->add('role', UserRoleType::class)
            ->getForm();
    }

    #[DataProvider('roleDateProvider')]
    public function testSubmitSuccess(string $role): void
    {
        $this->form->submit(['role' => $role]);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertTrue($this->form->isValid());
        $this->assertSame($this->form->get('role')->getData(), $role);
    }

    public function testSubmitFailed(): void
    {
        $this->form->submit(['role' => 'test']);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertFalse($this->form->isValid());
    }

    public function testGetBlockPrefix(): void
    {
        $userRoleType = new UserRoleType;

        $this->assertSame(UserRoleType::NAME, $userRoleType->getBlockPrefix());
    }

    public static function roleDateProvider(): Generator
    {
        yield [UserRoleConstant::STUDENT->value];
        yield [UserRoleConstant::OPPONENT->value];
        yield [UserRoleConstant::CONSULTANT->value];
    }
}
