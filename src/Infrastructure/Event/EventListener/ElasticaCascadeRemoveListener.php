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

namespace App\Infrastructure\Event\EventListener;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Work\Entity\Work;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use LogicException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ElasticaCascadeRemoveListener
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $identifiers = [];

    public function __construct(
        #[Autowire(service: 'fos_elastica.object_persister.task')]
        private readonly ObjectPersisterInterface $taskPersister,
        #[Autowire(service: 'fos_elastica.object_persister.conversation')]
        private readonly ObjectPersisterInterface $conversationPersister,
        #[Autowire(service: 'fos_elastica.object_persister.conversation_message')]
        private readonly ObjectPersisterInterface $conversationMessagePersister
    ) {}

    public function preRemove(PreRemoveEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();
        $connection = $eventArgs->getObjectManager()->getConnection();

        $conversationIds = [];
        if ($entity instanceof Work) {
            $workId = $entity->getId();
            /** @var list<int|string> $taskIds */
            $taskIds = $connection->fetchFirstColumn('SELECT id FROM task WHERE work_id = ?', [$workId]);
            /** @var list<int|string> $conversationIds */
            $conversationIds = $connection->fetchFirstColumn('SELECT id FROM conversation WHERE work_id = ?', [$workId]);

            $this->addIdentifiers('task', $taskIds);
            $this->addIdentifiers('conversation', $conversationIds);
        } elseif ($entity instanceof Conversation) {
            $conversationIds = [$entity->getId()];
        }

        if (count($conversationIds) === 0) {
            return;
        }

        /** @var list<int|string> $messageIds */
        $messageIds = $connection->fetchFirstColumn(
            'SELECT id FROM conversation_message WHERE conversation_id IN (?)',
            [$conversationIds],
            [ArrayParameterType::INTEGER]
        );

        $this->addIdentifiers('conversation_message', $messageIds);
    }

    public function postFlush(): void
    {
        $identifiers = $this->identifiers;
        $this->identifiers = [];

        foreach ($identifiers as $index => $ids) {
            $persister = match ($index) {
                'task' => $this->taskPersister,
                'conversation' => $this->conversationPersister,
                'conversation_message' => $this->conversationMessagePersister,
                default => throw new LogicException('Unsupported search index.')
            };

            $persister->deleteManyByIdentifiers(array_values($ids));
        }
    }

    /**
     * @param array<int|string> $ids
     */
    private function addIdentifiers(string $index, array $ids): void
    {
        foreach ($ids as $id) {
            $this->identifiers[$index][(int) $id] = (string) $id;
        }
    }
}
