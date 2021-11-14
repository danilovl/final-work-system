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

use App\Model\Task\Form\TaskForm;
use App\Model\Task\TaskModel;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactory;

class TaskFormTest extends KernelTestCase
{
    private FormFactory $formFactory;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->formFactory = $kernel->getContainer()->get('form.factory');
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSubmitValidData(
        array $data,
        bool $isValid
    ): void {
        $form = $this->formFactory->create(TaskForm::class, new TaskModel, [
            'csrf_protection' => false
        ]);
        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($isValid, $form->isValid());
    }

    public function dataProvider(): Generator
    {
        yield [
            [
                'name' => 'task name',
                'description' => 'some description',
                'complete' => false,
                'active' => true,
                'deadline' => '2022-01-22'
            ],
            true
        ];

        yield [
            [
                'name' => null,
                'description' => 'some description',
                'complete' => false,
                'active' => true,
                'deadline' => '2022-01-22'
            ],
            false
        ];

        yield [
            [
                'name' => 'task name',
                'description' => 'some description',
                'complete' => false,
                'active' => true,
                'deadline' => '00:00:00'
            ],
            false
        ];

        yield [
            [
                'name' => null,
                'description' => 'some description',
                'complete' => false,
                'active' => true,
                'deadline' => '00:00:00'
            ],
            false
        ];
    }
}
