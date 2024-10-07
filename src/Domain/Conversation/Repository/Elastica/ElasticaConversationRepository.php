<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\Conversation\Repository\Elastica;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\User\Entity\User;
use ArrayIterator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;

class ElasticaConversationRepository
{
    public function __construct(private readonly ConversationSearch $conversationSearch) {}

    public function getIdsByParticipantAndSearch(User $user, string $search): array
    {
        return $this->conversationSearch->getIdsByParticipantAndSearch($user, $search);
    }
}
