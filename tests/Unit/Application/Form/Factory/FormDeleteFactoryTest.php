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

namespace App\Tests\Unit\Application\Form\Factory;

use App\Application\Form\Factory\FormDeleteFactory;
use App\Domain\User\Entity\User;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use stdClass;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Routing\RouterInterface;

class FormDeleteFactoryTest extends TypeTestCase
{
    private FormDeleteFactory $formDeleteFactory;

    public function setUp(): void
    {
        parent::setUp();

        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->any())
            ->method('generate')
            ->willReturn('url');

        $hashids = $this->createMock(HashidsServiceInterface::class);
        $hashids->expects($this->any())
            ->method('encode')
            ->willReturn('Kt49gnG3');

        $this->formDeleteFactory = new FormDeleteFactory(
            $this->factory,
            $router,
            $hashids
        );
    }

    public function testCreateDeleteFormSuccess(): void
    {
        $user = new User;
        $user->setId(1);

        $this->formDeleteFactory->createDeleteForm($user, 'route');

        $this->assertTrue(true);
    }

    public function testCreateDeleteFormFailed(): void
    {
        $this->expectException(RuntimeException::class);

        $this->formDeleteFactory->createDeleteForm(new stdClass, 'route');
    }
}
