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
use App\Domain\Conversation\Facade\ConversationFacade;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\Work\Entity\Work;
use Doctrine\ORM\Event\{
    PostPersistEventArgs,
    PreUpdateEventArgs
};
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ElasticaConversationReindexListener
{
    /**
     * @var array<int, int>
     */
    private array $workIds = [];

    /**
     * @var array<int, Conversation>
     */
    private array $conversations = [];

    public function __construct(
        #[Autowire(service: 'fos_elastica.object_persister.conversation')]
        private readonly ObjectPersisterInterface $conversationPersister,
        private readonly ConversationFacade $conversationFacade
    ) {}

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();

        if (!$entity instanceof Work || !$eventArgs->hasChangedField('title')) {
            return;
        }

        $this->workIds[$entity->getId()] = $entity->getId();
    }

    public function postPersist(PostPersistEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();

        if (!$entity instanceof ConversationParticipant) {
            return;
        }

        $conversation = $entity->getConversation();
        $this->conversations[$conversation->getId()] = $conversation;
    }

    public function postFlush(): void
    {
        $conversations = $this->conversations;
        $workIds = array_values($this->workIds);
        $this->conversations = [];
        $this->workIds = [];

        if (count($workIds) > 0) {
            $workConversations = $this->conversationFacade->findAllByWorkIds($workIds);

            foreach ($workConversations as $conversation) {
                $conversations[$conversation->getId()] = $conversation;
            }
        }

        if (count($conversations) === 0) {
            return;
        }

        $this->conversationPersister->replaceMany(array_values($conversations));
    }
}
