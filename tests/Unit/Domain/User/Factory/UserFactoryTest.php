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

namespace App\Tests\Unit\Domain\User\Factory;

use App\Application\Service\EntityManagerService;
use App\Domain\User\Entity\User;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Model\UserModel;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactoryTest extends TestCase
{
    private UserFactory $userFactory;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerService::class);
        $userPasswordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userFactory = new UserFactory($entityManager, $userPasswordHasher);
    }

    public function testFlushFromModel(): void
    {
        $userModel = new UserModel;
        $userModel->degreeBefore = 'degreeBefore.';
        $userModel->firstName = 'firstName';
        $userModel->lastName = 'lastName';
        $userModel->degreeAfter = 'degreeAfter';
        $userModel->email = 'email';
        $userModel->emailCanonical = 'emailCanonical';
        $userModel->password = 'password';
        $userModel->username = 'username';
        $userModel->roles = [];
        $userModel->groups = new ArrayCollection([]);

        $user = new User;

        $entityManager = $this->createMock(EntityManagerService::class);
        $entityManager->expects($this->any())
            ->method('persistAndFlush')
            ->with($this->equalTo($user));

        $this->userFactory->flushFromModel($userModel, $user);

        $this->assertTrue(true);
    }
}
