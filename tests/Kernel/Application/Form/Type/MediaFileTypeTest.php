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

namespace App\Tests\Kernel\Application\Form\Type;

use App\Application\Form\Type\MediaFileType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;

class MediaFileTypeTest extends KernelTestCase
{
    private const string FILE_PATH_JPG = __DIR__ . '/../../../../Helper/image/test_jpg.jpg';
    private const string FILE_PATH_PNG = __DIR__ . '/../../../../Helper/image/test_png.png';

    private FormInterface $form;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $formFactory = $kernel->getContainer()->get('form.factory');

        $this->form = $formFactory->createBuilder(options: ['csrf_protection' => false])
            ->add('uploadMedia', MediaFileType::class, [
                'mimeTypes' => ['image/jpeg']
            ])
            ->getForm();
    }

    public function testSubmitSuccess(): void
    {
        $this->form->submit(['uploadMedia' => new File(self::FILE_PATH_JPG)]);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertTrue($this->form->isValid());
    }

    public function testSubmitFailed(): void
    {
        $this->form->submit(['uploadMedia' => new File(self::FILE_PATH_PNG)]);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertFalse($this->form->isValid());
    }
}
