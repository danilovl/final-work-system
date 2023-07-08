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

namespace App\Tests\Kernel\Domain\MediaCategory\Form;

use App\Domain\MediaCategory\Form\MediaCategoryForm;
use App\Domain\MediaCategory\Model\MediaCategoryModel;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactory;

class MediaCategoryFormTest extends KernelTestCase
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
        $form = $this->formFactory->create(MediaCategoryForm::class, new MediaCategoryModel, [
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
                'description' => 'some description'
            ],
            true
        ];

        yield [
            [
                'name' => null,
                'description' => 'some description'
            ],
            false
        ];
    }
}
