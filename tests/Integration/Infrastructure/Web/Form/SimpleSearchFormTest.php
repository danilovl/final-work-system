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

namespace App\Tests\Integration\Infrastructure\Web\Form;

use App\Application\Model\SearchModel;
use App\Infrastructure\Web\Form\SimpleSearchForm;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;

class SimpleSearchFormTest extends KernelTestCase
{
    private FormInterface $form;

    private SearchModel $searchModel;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $formFactory = $kernel->getContainer()->get('form.factory');

        $this->searchModel = new SearchModel;

        $this->form = $formFactory
            ->createBuilder(SimpleSearchForm::class, $this->searchModel)
            ->getForm();
    }

    public function testSubmitSuccess(): void
    {
        $this->form->submit(['search' => 'search text']);

        $this->assertTrue($this->form->isSynchronized());
        $this->assertTrue($this->form->isValid());
        $this->assertSame('search text', $this->searchModel->search);
    }
}
