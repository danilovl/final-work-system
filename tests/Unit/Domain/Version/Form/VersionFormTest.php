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

namespace App\Tests\Unit\Domain\Version\Form;

use App\Domain\Media\Model\MediaModel;
use App\Domain\Version\Form\VersionForm;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

class VersionFormTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    public function testBuildForm(): void
    {
        $mediaModel = new MediaModel;

        $form = $this->factory->create(VersionForm::class, $mediaModel, [
            'mimeTypes' => ['image/png'],
            'uploadMedia' => true
        ]);

        $form->submit([
            'name' => 'Test Name',
            'description' => 'Test Description'
        ]);

        $this->assertTrue($form->isSynchronized());

        $view = $form->createView();
        $children = $view->children;

        $this->assertArrayHasKey('name', $children);
        $this->assertArrayHasKey('description', $children);
        $this->assertArrayHasKey('uploadMedia', $children);

        $this->assertSame('Test Name', $mediaModel->name);
        $this->assertSame('Test Name', $mediaModel->name);
    }
}
