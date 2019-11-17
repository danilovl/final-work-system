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

namespace FinalWork\FinalWorkBundle\Services;

use Doctrine\ORM\{
    ORMException,
    OptimisticLockException,
    NonUniqueResultException,
    ORMInvalidArgumentException
};
use FinalWork\FinalWorkBundle\Entity\{
    Conversation,
    ConversationMessage,
    ConversationMessageStatus,
    ConversationMessageStatusType
};
use FinalWork\FinalWorkBundle\Constant\ConversationMessageStatusTypeConstant;
use FinalWork\FinalWorkBundle\Entity\Repository\ConversationMessageStatusRepository;
use FinalWork\SonataUserBundle\Entity\User;

class ConversationStatusService
{
    /**
     * @var EntityManagerService
     */
    private $em;

    /**
     * @var ConversationMessageStatusRepository
     */
    private $conversationMessageStatusRepository;

    /**
     * ConversationStatusService constructor.
     * @param EntityManagerService $entityManagerService
     */
    public function __construct(EntityManagerService $entityManagerService)
    {
        $this->em = $entityManagerService;
        $this->conversationMessageStatusRepository = $entityManagerService->getRepository(ConversationMessageStatus::class);
    }

    /**
     * @param Conversation $conversation
     * @param User $user
     * @return bool
     *
     * @throws ORMException
     */
    public function isConversationRead(
        Conversation $conversation,
        User $user
    ): bool {
        $conversationStatus = $this->conversationMessageStatusRepository
            ->findOneByConversationUserType(
                $conversation,
                $user,
                $this->em->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::UNREAD
                )
            )
            ->getQuery()
            ->getResult();

        return empty($conversationStatus);
    }

    /**
     * @param Conversation $conversation
     * @param User $user
     * @param $status
     *
     * @throws ORMInvalidArgumentException
     * @throws ORMException
     */
    public function changeConversationStatus(
        Conversation $conversation,
        User $user,
        $status
    ): void {
        /** @var ConversationMessageStatus|null $conversationStatus */
        $conversationStatus = $this->conversationMessageStatusRepository
            ->findOneByConversationUser($conversation, $user)
            ->getQuery()
            ->getOneOrNullResult();

        if ($conversationStatus !== null) {
            $conversationStatus->setType($this->em->getReference(ConversationMessageStatusType::class, $status));

            $this->em->persistAndFlush($conversationStatus);
        }
    }

    /**
     * @param ConversationMessage $conversationMessage
     * @param User $user
     * @param bool $switchToRead
     * @return bool
     *
     * @throws ORMInvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function isConversationMessageRead(
        ConversationMessage $conversationMessage,
        User $user,
        $switchToRead = false
    ): bool {
        /** @var ConversationMessageStatus|null $conversationMessageStatus */
        $conversationMessageStatus = $this->conversationMessageStatusRepository
            ->findOneByMessageUser($conversationMessage, $user)
            ->getQuery()
            ->getOneOrNullResult();

        if ($conversationMessageStatus !== null) {
            if ($conversationMessageStatus->getType()->getId() === ConversationMessageStatusTypeConstant::READ) {
                return true;
            }

            if ($switchToRead === true) {
                $conversationMessageStatus->setType($this->em->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::READ
                ));

                $this->em->persistAndFlush($conversationMessageStatus);
            }

            return false;
        }

        return true;
    }

    /**
     * @param ConversationMessage $conversationMessage
     * @param User $user
     * @param $status
     *
     * @throws ORMInvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function changeConversationMessageStatus(
        ConversationMessage $conversationMessage,
        User $user,
        $status
    ): void {
        /** @var ConversationMessageStatus|null $conversationMessageStatus */
        $conversationMessageStatus = $this->conversationMessageStatusRepository
            ->findOneByMessageUser($conversationMessage, $user)
            ->getQuery()
            ->getOneOrNullResult();

        if ($conversationMessageStatus !== null) {
            $conversationMessageStatus->setType($this->em->getReference(ConversationMessageStatusType::class, $status));

            $this->em->persistAndFlush($conversationMessageStatus);
        }
    }

    /**
     * @param ConversationMessage $conversationMessage
     * @param User $user
     *
     * @throws ORMInvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function setReadConversationMessage(
        ConversationMessage $conversationMessage,
        User $user
    ): void {
        $this->changeConversationMessageStatus($conversationMessage, $user, ConversationMessageStatusTypeConstant::READ);
    }

    /**
     * @param ConversationMessage $conversationMessage
     * @param User $user
     * @return ConversationMessageStatus|null
     * @throws NonUniqueResultException
     */
    public function getConversationMessageStatus(
        ConversationMessage $conversationMessage,
        User $user
    ): ?ConversationMessageStatus {
        return $this->conversationMessageStatusRepository
            ->findOneByMessageUser($conversationMessage, $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
