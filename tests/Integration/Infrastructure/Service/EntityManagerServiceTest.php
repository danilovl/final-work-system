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

namespace App\Tests\Integration\Infrastructure\Service;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Repository\ConversationRepository;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\Repository\TaskRepository;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Repository\WorkRepository;
use App\Infrastructure\Service\EntityManagerService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EntityManagerServiceTest extends KernelTestCase
{
    private EntityManagerService $entityManagerService;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManagerService = $kernel->getContainer()->get(EntityManagerService::class);
    }

    /**
     * @param class-string<object> $entityClass
     * @param class-string $repositoryClass
     */
    #[DataProvider('repositoryProvider')]
    public function testRepository(string $entityClass, string $repositoryClass): void
    {
        $repository = $this->entityManagerService->getRepository($entityClass);
        $this->assertEquals(get_class($repository), $repositoryClass);
    }

    /**
     * @param class-string<object> $entityClass
     */
    #[DataProvider('referenceProvider')]
    public function testReference(string $entityClass): void
    {
        $reference = $this->entityManagerService->getReference($entityClass, 1);
        $this->assertNotNull($reference, "Reference should not be null");
        $this->assertEquals(get_parent_class($reference), $entityClass);
    }

    public static function repositoryProvider(): Generator
    {
        yield [User::class, UserRepository::class];
        yield [Work::class, WorkRepository::class];
        yield [Task::class, TaskRepository::class];
        yield [Conversation::class, ConversationRepository::class];
    }

    public static function referenceProvider(): Generator
    {
        yield [User::class];
        yield [Work::class];
        yield [Task::class];
        yield [Conversation::class];
    }
}
