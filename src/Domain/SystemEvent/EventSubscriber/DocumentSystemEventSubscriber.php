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

namespace App\Domain\SystemEvent\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\Media\EventDispatcher\GenericEvent\MediaGenericEvent;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventType\Constant\SystemEventTypeConstant;
use App\Domain\User\Entity\User;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Domain\Work\Bus\Query\ActiveAuthors\{GetActiveAuthorsQuery, GetActiveAuthorsQueryResult};

class DocumentSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        EntityManagerService $entityManagerService,
        QueryBusInterface $queryBus,
    ) {
        parent::__construct($entityManagerService, $queryBus);
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::DOCUMENT_CREATE => 'onDocumentCreate'
        ];
    }

    public function onDocumentCreate(MediaGenericEvent $event): void
    {
        $media = $event->media;
        $owner = $media->getOwner();

        $systemEventType = $this->getSystemEventType(SystemEventTypeConstant::DOCUMENT_CREATE->value);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($owner);
        $systemEvent->setMedia($media);
        $systemEvent->setType($systemEventType);

        $getActiveAuthorsQuery = GetActiveAuthorsQuery::create(
            user: $owner,
            userType: WorkUserTypeConstant::SUPERVISOR->value
        );
        /** @var GetActiveAuthorsQueryResult $getActiveAuthorsQueryResult */
        $getActiveAuthorsQueryResult = $this->queryBus->handle($getActiveAuthorsQuery);
        $recipientArray = $getActiveAuthorsQueryResult->authors;

        /** @var User $user */
        foreach ($recipientArray as $user) {
            $recipientAuthor = new SystemEventRecipient;
            $recipientAuthor->setRecipient($user);
            $systemEvent->addRecipient($recipientAuthor);
        }

        $this->entityManagerService->persistAndFlush($systemEvent);
    }
}
