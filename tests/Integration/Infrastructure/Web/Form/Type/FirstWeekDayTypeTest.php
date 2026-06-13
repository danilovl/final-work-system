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

namespace App\Tests\Integration\Infrastructure\Web\Form\Type;

use App\Infrastructure\Web\Form\Type\FirstWeekDayType;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;

class FirstWeekDayTypeTest extends KernelTestCase
{
    private FormInterface $form;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $formFactory = $kernel->getContainer()->get('form.factory');

        $this->form = $formFactory->createBuilder(options: ['csrf_protection' => false])
            ->add('start', FirstWeekDayType::class)
            ->getForm();
    }

    #[DataProvider('provideSubmitSuccessCases')]
    public function testSubmitSuccess(string $date): void
    {
        $this->form->submit(['start' => $date]);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertTrue($this->form->isValid());
    }

    #[DataProvider('provideSubmitFailedCases')]
    public function testSubmitFailed(string $date): void
    {
        $this->form->submit(['start' => $date]);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertFalse($this->form->isValid());
    }

    public function testGetBlockPrefix(): void
    {
        $userRoleType = new FirstWeekDayType;

        $this->assertSame(FirstWeekDayType::NAME, $userRoleType->getBlockPrefix());
    }

    public static function provideSubmitSuccessCases(): Generator
    {
        yield ['2023-07-03'];
        yield ['2023-07-10'];
        yield ['2023-07-17'];
        yield ['2023-07-24'];
        yield ['2023-07-31'];
    }

    public static function provideSubmitFailedCases(): Generator
    {
        yield ['2023-07-05'];
        yield ['2023-07-14'];
        yield ['2023-07-18'];
        yield ['2023-07-25'];
        yield ['2023-07-30'];
    }
}
