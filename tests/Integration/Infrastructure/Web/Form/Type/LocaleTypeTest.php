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

use App\Application\Constant\LocaleConstant;
use App\Infrastructure\Web\Form\Type\LocaleType;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;

class LocaleTypeTest extends KernelTestCase
{
    private FormInterface $form;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $formFactory = $kernel->getContainer()->get('form.factory');

        $this->form = $formFactory->createBuilder(options: ['csrf_protection' => false])
            ->add('locale', LocaleType::class)
            ->getForm();
    }

    #[DataProvider('provideSubmitSuccessCases')]
    public function testSubmitSuccess(string $locale): void
    {
        $this->form->submit(['locale' => $locale]);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertTrue($this->form->isValid());
        $this->assertSame($this->form->get('locale')->getData(), $locale);
    }

    public function testSubmitFailed(): void
    {
        $this->form->submit(['locale' => 'test']);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertFalse($this->form->isValid());
    }

    public function testGetBlockPrefix(): void
    {
        $userRoleType = new LocaleType('');

        $this->assertSame(LocaleType::NAME, $userRoleType->getBlockPrefix());
    }

    public static function provideSubmitSuccessCases(): Generator
    {
        foreach (LocaleConstant::values() as $locale) {
            yield [$locale];
        }
    }
}
