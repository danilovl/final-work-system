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

namespace App\Tests\Services;

use App\Entity\{
    Task,
    User,
    Work,
    Conversation
};
use App\Repository\{
    TaskRepository,
    UserRepository,
    WorkRepository,
    ConversationRepository
};
use App\Service\EntityManagerService;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EntityManagerServiceTest extends KernelTestCase
{
    private EntityManagerService $entityManagerService;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManagerService = $kernel->getContainer()->get('app.entity_manager');
    }

    /**
     * @dataProvider repositoryProvider
     */
    public function testRepository(string $entityClass, string $repositoryClass): void
    {
        $repository = $this->entityManagerService->getRepository($entityClass);
        $this->assertEquals(get_class($repository), $repositoryClass);
    }

    /**
     * @dataProvider referenceProvider
     */
    public function testReference(string $entityClass): void
    {
        $reference = $this->entityManagerService->getReference($entityClass, 1);
        $this->assertEquals(get_parent_class($reference), $entityClass);
    }

    public function repositoryProvider(): Generator
    {
        yield [User::class, UserRepository::class];
        yield [Work::class, WorkRepository::class];
        yield [Task::class, TaskRepository::class];
        yield [Conversation::class, ConversationRepository::class];
    }

    public function referenceProvider(): Generator
    {
        yield [User::class];
        yield [Work::class];
        yield [Task::class];
        yield [Conversation::class];
    }
}
