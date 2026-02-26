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

use App\Infrastructure\Web\Form\Type\WeekDaysType;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;

class WeekDaysTypeTest extends KernelTestCase
{
    private FormInterface $form;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $formFactory = $kernel->getContainer()->get('form.factory');

        $this->form = $formFactory->createBuilder(options: ['csrf_protection' => false])
            ->add('day', WeekDaysType::class)
            ->getForm();
    }

    #[DataProvider('dayDateProvider')]
    public function testSubmitSuccess(int $number): void
    {
        $this->form->submit(['day' => $number]);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertTrue($this->form->isValid());
        $this->assertSame($this->form->get('day')->getData(), $number);
    }

    public function testSubmitFailed(): void
    {
        $this->form->submit(['day' => 7]);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertFalse($this->form->isValid());
    }

    public function testGetBlockPrefix(): void
    {
        $userRoleType = new WeekDaysType;

        $this->assertSame(WeekDaysType::NAME, $userRoleType->getBlockPrefix());
    }

    public static function dayDateProvider(): Generator
    {
        foreach (range(0, 6) as $number) {
            yield [$number];
        }
    }
}
