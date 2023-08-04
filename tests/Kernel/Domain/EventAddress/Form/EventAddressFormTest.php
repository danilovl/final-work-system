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

namespace App\Tests\Kernel\Domain\EventAddress\Form;

use App\Domain\EventAddress\Form\EventAddressForm;
use App\Domain\EventAddress\Model\EventAddressModel;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactory;

class EventAddressFormTest extends KernelTestCase
{
    private FormFactory $formFactory;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->formFactory = $kernel->getContainer()->get('form.factory');
    }

    #[DataProvider('dataProvider')]
    public function testSubmitValidData(
        array $data,
        bool $isValid
    ): void {
        $form = $this->formFactory->create(EventAddressForm::class, new EventAddressModel, [
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
                'name' => 'task name',
                'description' => 'some description',
                'street' => 'street address',
                'latitude' => '33.0205889',
                'longitude' => '24.4936111',
                'skype' => false,
            ],
            true
        ];

        yield [
            [
                'name' => null,
                'description' => 'some description',
                'street' => 'street address',
                'latitude' => '33.0205889',
                'longitude' => '24.4936111',
                'skype' => false,
            ],
            false
        ];
    }
}
