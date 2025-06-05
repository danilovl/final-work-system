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
use Doctrine\ORM\{
    EntityRepository,
    Mapping as ORM,
    EntityManagerInterface
};
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class EntityManagerServiceTest extends TestCase
{
    private EntityManagerService $entityManagerService;

    private MockObject&EntityManagerInterface $entityManager;

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
    }

    public function testRemoveNativeSql(): void
    {
        $connection = $this->createMock(Connection::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $connection
            ->expects($this->once())
            ->method('executeQuery');

        $entity = new #[ORM\Table(name: 'test_table')] #[ORM\Entity] class {};

        $this->entityManagerService->removeNativeSql($entity::class, 1);
    }

    public function testDetach(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('detach');

        $this->entityManagerService->detach(new stdClass);
    }

    public function testDetachArray(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('detach');

        $this->entityManagerService->detachArray([new User]);
    }

    public function testRefresh(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('refresh');

        $this->entityManagerService->refresh(new stdClass);
    }

    public function testFlush(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->entityManagerService->flush();
    }

    public function testGetRepository(): void
    {
        $objectRepository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($objectRepository);

        $this->entityManagerService->getRepository(stdClass::class);
    }

    public function testClear(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('clear');

        $this->entityManagerService->clear();
    }

    public function testGetConnection(): void
    {
        $connection = $this->createMock(Connection::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $this->entityManagerService->getConnection();
    }
}
