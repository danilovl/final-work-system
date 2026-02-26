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

namespace App\Tests\Integration\Domain\Work\Form;

use App\Domain\User\Entity\User;
use App\Domain\Work\Form\WorkUserForm;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactory;

class WorkUserFormTest extends KernelTestCase
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
        $form = $this->formFactory->create(WorkUserForm::class, new User, [
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
                'email' => 'email@test.com'
            ],
            true
        ];

        yield [
            [
                'degreeBefore' => 'Ing.',
                'firstName' => 'first name',
                'lastName' => 'last name',
                'degreeAfter' => 'PhD.',
                'phone' => null,
                'email' => null
            ],
            false
        ];

        yield [
            [
                'degreeBefore' => 'Ing.',
                'firstName' => null,
                'lastName' => 'last name',
                'degreeAfter' => 'PhD.',
                'phone' => null,
                'email' => 'email@test.com'
            ],
            false
        ];

        yield [
            [
                'degreeBefore' => 'Ing.',
                'firstName' => 'first name',
                'lastName' => null,
                'degreeAfter' => 'PhD.',
                'phone' => null,
                'email' => 'email@test.com'
            ],
            false
        ];

        yield [
            [
                'degreeBefore' => 'Ing.',
                'firstName' => 'first name',
                'lastName' => null,
                'degreeAfter' => null,
                'phone' => null,
                'email' => 'email@test.com'
            ],
            false
        ];
    }
}
