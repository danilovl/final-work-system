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

namespace App\Domain\ConversationMessage\Factory;

use App\Infrastructure\Constant\DateFormatConstant;
use App\Domain\ConversationMessage\DTO\Api\WorkConversationMessageDTO;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatus\DTO\Api\ConversationMessageStatusDTO;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationMessageStatusType\DTO\Api\ConversationMessageStatusTypeDTO;
use App\Domain\Work\Factory\WorkDetailDTOFactory;

readonly class WorkConversationMessageDTOFactory
{
    public function __construct(private WorkDetailDTOFactory $workDetailDTOFactory) {}

    public function create(ConversationMessage $message, bool $isRead): WorkConversationMessageDTO
    {
        $statuses = [];

        /** @var ConversationMessageStatus $status */
        foreach ($message->getStatuses() as $status) {
            $type = $status->getType();

            $statuses[] = new ConversationMessageStatusDTO(
                id: $status->getId(),
                type: new ConversationMessageStatusTypeDTO(
                    id: $type->getId(),
                    name: $type->getName(),
                    description: $type->getDescription()
                ),
                user: $this->workDetailDTOFactory->createUser($status->getUser())
            );
        }

        return new WorkConversationMessageDTO(
            id: $message->getId(),
            read: $isRead,
            content: $message->getContent(),
            owner: $this->workDetailDTOFactory->createUser($message->getOwner()),
            statuses: $statuses,
            createdAt: $message->getCreatedAt()->format(DateFormatConstant::DATABASE->value)
        );
    }
}
