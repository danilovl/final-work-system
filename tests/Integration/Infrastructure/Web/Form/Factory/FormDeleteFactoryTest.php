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

namespace App\Tests\Integration\Infrastructure\Web\Form\Factory;

use App\Domain\User\Entity\User;
use App\Infrastructure\Web\Form\Factory\FormDeleteFactory;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;

class FormDeleteFactoryTest extends KernelTestCase
{
    private FormDeleteFactory $formDeleteFactory;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        /** @var FormFactoryInterface $formFactory */
        $formFactory = $kernel->getContainer()->get('form.factory');

        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')
            ->willReturn('url');

        $hashids = $this->createStub(HashidsServiceInterface::class);
        $hashids->method('encode')
            ->willReturn('Kt49gnG3');

        $this->formDeleteFactory = new FormDeleteFactory(
            $formFactory,
            $router,
            $hashids
        );
    }

    public function testCreateDeleteFormSuccess(): void
    {
        $this->expectNotToPerformAssertions();

        $user = new User;
        $user->setId(1);

        $this->formDeleteFactory->createDeleteForm($user, 'route');
    }

    public function testCreateDeleteFormFailed(): void
    {
        $this->expectException(RuntimeException::class);

        $this->formDeleteFactory->createDeleteForm(new stdClass, 'route');
    }
}
