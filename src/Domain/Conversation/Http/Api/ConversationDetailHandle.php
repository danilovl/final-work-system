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

namespace App\Domain\Conversation\Http\Api;

use App\Application\Mapper\ObjectToDtoMapper;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\DTO\Api\ConversationDTO;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationParticipant\DTO\Api\ParticipantDTO;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\User\Service\UserService;
use App\Domain\Work\DTO\Api\WorkDTO;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class ConversationDetailHandle
{
    public function __construct(
        private UserService $userService,
        private ConversationMessageFacade $conversationMessageFacade,
        private ObjectToDtoMapper $objectToDtoMapper
    ) {}

    public function __invoke(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $this->userService->getUser();

        ConversationHelper::getConversationOpposite([$conversation], $user);

        $conversationMessagesQuery = $this->conversationMessageFacade
            ->queryMessagesByConversation($conversation);

        $conversationMessagesQuery->setHydrationMode(ConversationMessage::class);
        /** @var ConversationMessage[] $conversationMessages */
        $conversationMessages = $conversationMessagesQuery->getResult();

        $this->conversationMessageFacade->setIsReadToConversationMessages($conversationMessages, $user);

        $participantsCollection = $conversation->getParticipantsExceptUsers([$user]);
        $participants = [];
        
        /** @var ConversationParticipant $participant */
        foreach ($participantsCollection as $participant) {
            $userDto = $this->objectToDtoMapper->map($participant->getUser(), UserDTO::class);
            
            $participants[] = new ParticipantDTO(
                id: $participant->getId(),
                user: $userDto
            );
        }

        $recipientDto = null;
        if ($conversation->getRecipient() !== null) {
            $recipientDto = $this->objectToDtoMapper->map($conversation->getRecipient(), UserDTO::class);
        }

        $workDto = null;
        if ($conversation->getWork() !== null) {
            $workDto = $this->objectToDtoMapper->map(
                entity: $conversation->getWork(),
                dtoClass: WorkDTO::class,
                ignoreGroups: ['user:read:author', 'user:read:supervisor', 'user:read:opponent', 'user:read:consultant']
            );
        }

        $conversationDto = new ConversationDTO(
            id: $conversation->getId(),
            name: $conversation->getName(),
            isRead: $conversation->isRead(),
            recipient: $recipientDto,
            work: $workDto,
            participants: $participants
        );

        return new JsonResponse($conversationDto);
    }
}
