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

namespace App\Tests\Kernel\Application\Helper;

use App\Application\Helper\FormValidationMessageHelper;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\{
    DateType,
    TextType
};
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints\{
    Date,
    NotBlank
};

class FormValidationMessageHelperTest extends KernelTestCase
{
    private FormFactory $formFactory;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->formFactory = $kernel->getContainer()->get('form.factory');
    }

    #[DataProvider('dataProvider')]
    public function testGetErrorMessages(array $submitData, array $expectedErrors): void
    {
        $form = $this->formFactory
            ->createBuilder(FormType::class, null, [
                'csrf_protection' => false
            ])
            ->add('text', TextType::class, [
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('date', DateType::class, [
                'constraints' => [
                    new NotBlank,
                    new Date
                ]
            ])
            ->getForm();

        $form->submit($submitData);

        $result = FormValidationMessageHelper::getErrorMessages($form);

        $this->assertEquals($expectedErrors, $result);
    }

    public static function dataProvider(): Generator
    {
        yield [
            ['text' => null, 'date' => null],
            ['text' => ['This value should not be blank.'], 'date' => ['This value should not be blank.']]
        ];

        yield [
            ['text' => null, 'date' => 'text'],
            [
                'text' => ['This value should not be blank.'],
                'date' => [
                    'Please enter a valid date.',
                    'year' => [],
                    'month' => [],
                    'day' => []
                ]
            ]
        ];
    }
}
