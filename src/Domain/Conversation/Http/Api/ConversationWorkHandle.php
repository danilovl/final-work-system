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

use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use App\Domain\Conversation\DTO\Api\ConversationDTO;
use App\Domain\Conversation\Facade\ConversationFacade;
use App\Domain\ConversationParticipant\DTO\Api\ParticipantDTO;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\ConversationType\DTO\Api\ConversationTypeDTO;
use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\User\Service\UserService;
use App\Domain\Work\DTO\Api\WorkDTO;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class ConversationWorkHandle
{
    public function __construct(
        private UserService $userService,
        private ConversationFacade $conversationFacade,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(Request $request, Work $work): JsonResponse
    {
        $user = $this->userService->getUser();
        $conversation = $this->conversationFacade->findByWorkUser($work, $user);

        if ($conversation === null) {
            throw new NotFoundHttpException;
        }

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
                $conversation->getWork(),
                WorkDTO::class,
                ignoreGroups: ['user:read:author', 'user:read:supervisor', 'user:read:opponent', 'user:read:consultant']
            );
        }

        $typeDto = new ConversationTypeDTO(
            id: $conversation->getType()->getId(),
            name: $conversation->getType()->getName(),
            constant: $conversation->getType()->getConstant()
        );

        $conversationDto = new ConversationDTO(
            id: $conversation->getId(),
            name: $conversation->getName(),
            isRead: $conversation->isRead(),
            recipient: $recipientDto,
            work: $workDto,
            participants: $participants,
            type: $typeDto
        );

        return new JsonResponse($conversationDto);
    }
}
