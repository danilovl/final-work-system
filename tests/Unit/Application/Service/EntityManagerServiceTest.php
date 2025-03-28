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

namespace App\Tests\Unit\Application\Service;

use App\Application\EventDispatcher\EntityEventDispatcher;
use App\Application\Service\EntityManagerService;
use App\Domain\User\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use stdClass;

class EntityManagerServiceTest extends TestCase
{
    private EntityManagerService $entityManagerService;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $entityEventDispatcherService = $this->createMock(EntityEventDispatcher::class);

        $this->entityManagerService = new EntityManagerService(
            $this->entityManager,
            $entityEventDispatcherService
        );
    }

    public function testGetReference(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getReference');

        $this->entityManagerService->getReference(stdClass::class, 1);

        $this->assertTrue(true);
    }

    public function testPersistAndFlush(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->entityManagerService->persistAndFlush(new stdClass);

        $this->assertTrue(true);
    }

    public function testRemove(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('remove');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->entityManagerService->remove(new stdClass);

        $this->assertTrue(true);
    }

    public function testDetach(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('detach');

        $this->entityManagerService->detach(new stdClass);

        $this->assertTrue(true);
    }

    public function testDetachArray(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('detach');

        $this->entityManagerService->detachArray([new User]);

        $this->assertTrue(true);
    }

    public function testRefresh(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('refresh');

        $this->entityManagerService->refresh(new stdClass);

        $this->assertTrue(true);
    }

    public function testFlush(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->entityManagerService->flush();

        $this->assertTrue(true);
    }

    public function testGetRepository(): void
    {
        $objectRepository = $this->createMock(ObjectRepository::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($objectRepository);

        $this->entityManagerService->getRepository(stdClass::class);

        $this->assertTrue(true);
    }

    public function testClear(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('clear');

        $this->entityManagerService->clear();

        $this->assertTrue(true);
    }

    public function testGetConnection(): void
    {
        $connection = $this->createMock(Connection::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $this->entityManagerService->getConnection();

        $this->assertTrue(true);
    }
}
