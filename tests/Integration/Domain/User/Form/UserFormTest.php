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

namespace App\Tests\Integration\Domain\User\Form;

use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\Form\UserForm;
use App\Domain\User\Model\UserModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactory;

class UserFormTest extends KernelTestCase
{
    private FormFactory $formFactory;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->formFactory = $kernel->getContainer()->get('form.factory');
    }

    #[DataProvider('dataProvider')]
    public function testSubmitValidData(
        array $data,
        bool $isValid
    ): void {
        $form = $this->formFactory->create(UserForm::class, new UserModel, [
            'csrf_protection' => false
        ]);
        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($isValid, $form->isValid());
    }

    public static function dataProvider(): Generator
    {
        yield [
            [
                'degreeBefore' => null,
                'firstName' => 'first name',
                'lastName' => 'last name',
                'degreeAfter' => null,
                'phone' => null,
                'email' => 'email@test.com',
                'username' => 'test_username',
                'role' => UserRoleConstant::STUDENT->value,
                'groups' => null
            ],
            true
        ];

        yield [
            [
                'degreeBefore' => null,
                'firstName' => 'first name',
                'lastName' => 'last name',
                'degreeAfter' => null,
                'phone' => null,
                'email' => null,
                'username' => 'test_username',
                'role' => UserRoleConstant::STUDENT->value,
                'groups' => null
            ],
            false
        ];

        yield [
            [
                'degreeBefore' => null,
                'firstName' => 'first name',
                'lastName' => 'last name',
                'degreeAfter' => null,
                'phone' => null,
                'email' => 'email@test.com',
                'username' => null,
                'role' => UserRoleConstant::STUDENT->value,
                'groups' => null
            ],
            false
        ];

        yield [
            [
                'degreeBefore' => null,
                'firstName' => 'first name',
                'lastName' => 'last name',
                'degreeAfter' => null,
                'phone' => null,
                'email' => 'email@test.com',
                'username' => null,
                'role' => null,
                'groups' => null
            ],
            false
        ];
    }
}
