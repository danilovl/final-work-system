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

namespace App\Tests\Unit\Domain\Work\EventListener\Entity;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\EventListener\Entity\ElasticaCascadeRemoveListener;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ElasticaCascadeRemoveListenerTest extends TestCase
{
    /**
     * @param int[] $taskIds
     * @param array<int, int[]> $conversationMessageIds
     * @param array<string, string[]> $expected
     */
    #[DataProvider('provideDependentDocumentsAreDeletedAfterFlushCases')]
    public function testDependentDocumentsAreDeletedAfterFlush(
        object $entity,
        array $taskIds,
        array $conversationMessageIds,
        array $expected
    ): void {
        $deleted = [];
        $listener = new ElasticaCascadeRemoveListener(
            taskPersister: $this->createPersister('task', $deleted),
            conversationPersister: $this->createPersister('conversation', $deleted),
            conversationMessagePersister: $this->createPersister('conversation_message', $deleted)
        );

        $entityManager = $this->createEntityManager(
            taskIds: $taskIds,
            conversationMessageIds: $conversationMessageIds
        );

        $listener->preRemove(new PreRemoveEventArgs($entity, $entityManager));

        $this->assertSame([], $deleted);

        $listener->postFlush();
        $listener->postFlush();

        $this->assertSame($expected, $deleted);
    }

    public static function provideDependentDocumentsAreDeletedAfterFlushCases(): Generator
    {
        yield 'work removes its tasks, conversations and their messages' => [
            'entity' => self::createWork(1),
            'taskIds' => [1, 2],
            'conversationMessageIds' => [10 => [100, 101], 11 => [110]],
            'expected' => [
                'task' => ['1', '2'],
                'conversation' => ['10', '11'],
                'conversation_message' => ['100', '101', '110']
            ]
        ];

        yield 'work without dependent entities' => [
            'entity' => self::createWork(1),
            'taskIds' => [],
            'conversationMessageIds' => [],
            'expected' => []
        ];

        yield 'conversation removes its messages' => [
            'entity' => self::createConversation(10),
            'taskIds' => [],
            'conversationMessageIds' => [10 => [100, 101], 11 => [110]],
            'expected' => [
                'conversation_message' => ['100', '101']
            ]
        ];

        yield 'other entity is ignored' => [
            'entity' => new stdClass,
            'taskIds' => [1],
            'conversationMessageIds' => [10 => [100]],
            'expected' => []
        ];
    }

    /**
     * @param int[] $taskIds
     * @param array<int, int[]> $conversationMessageIds
     */
    private function createEntityManager(array $taskIds, array $conversationMessageIds): EntityManagerInterface
    {
        $connection = $this->createStub(Connection::class);
        $connection
            ->method('fetchFirstColumn')
            ->willReturnCallback(static function (string $sql, array $params) use ($taskIds, $conversationMessageIds): array {
                if (str_contains($sql, 'FROM task')) {
                    return $taskIds;
                }

                if (str_contains($sql, 'FROM conversation_message')) {
                    $messageIds = [];
                    foreach ($params[0] as $conversationId) {
                        array_push($messageIds, ...$conversationMessageIds[$conversationId] ?? []);
                    }

                    return $messageIds;
                }

                return array_keys($conversationMessageIds);
            });

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager
            ->method('getConnection')
            ->willReturn($connection);

        return $entityManager;
    }

    /**
     * @param array<string, string[]> $deleted
     */
    private function createPersister(string $index, array &$deleted): ObjectPersisterInterface
    {
        $persister = $this->createStub(ObjectPersisterInterface::class);
        $persister
            ->method('deleteManyByIdentifiers')
            ->willReturnCallback(static function (array $identifiers) use ($index, &$deleted): void {
                $deleted[$index] = $identifiers;
            });

        return $persister;
    }

    private static function createWork(int $id): Work
    {
        $work = new Work;
        $work->setId($id);

        return $work;
    }

    private static function createConversation(int $id): Conversation
    {
        $conversation = new Conversation;
        $conversation->setId($id);

        return $conversation;
    }
}
