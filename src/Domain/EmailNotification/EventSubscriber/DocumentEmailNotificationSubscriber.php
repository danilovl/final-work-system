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

namespace App\Domain\EmailNotification\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use App\Domain\EmailNotification\Provider\{
    EmailNotificationAddToQueueProvider,
    EmailNotificationEnableMessengerProvider
};
use Override;
use App\Infrastructure\Service\{
    TranslatorService,
    TwigRenderService
};
use App\Domain\EmailNotification\Factory\EmailNotificationFactory;
use App\Domain\Media\EventDispatcher\GenericEvent\MediaGenericEvent;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Domain\Work\Bus\Query\ActiveAuthors\{GetActiveAuthorsQuery, GetActiveAuthorsQueryResult};

class DocumentEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected QueryBusInterface $queryBus,
        protected TwigRenderService $twigRenderService,
        protected TranslatorService $translator,
        protected EmailNotificationFactory $emailNotificationFactory,
        protected ParameterServiceInterface $parameterService,
        protected MessageBusInterface $bus,
        protected EmailNotificationAddToQueueProvider $emailNotificationAddToQueueProvider,
        protected EmailNotificationEnableMessengerProvider $emailNotificationEnableMessengerProvider
    ) {
        parent::__construct(
            $queryBus,
            $twigRenderService,
            $translator,
            $emailNotificationFactory,
            $parameterService,
            $bus,
            $emailNotificationAddToQueueProvider,
            $emailNotificationEnableMessengerProvider
        );
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

        $getActiveAuthorsQuery = GetActiveAuthorsQuery::create(
            user: $owner,
            userType: WorkUserTypeConstant::SUPERVISOR->value
        );
        /** @var GetActiveAuthorsQueryResult $getActiveAuthorsQueryResult */
        $getActiveAuthorsQueryResult = $this->queryBus->handle($getActiveAuthorsQuery);
        $recipientArray = $getActiveAuthorsQueryResult->authors;

        $templateParameters = [
            'mediaOwner' => $media->getOwner()->getFullNameDegree(),
            'mediaName' => $media->getName()
        ];

        foreach ($recipientArray as $user) {
            $emailNotificationToQueueData = new EmailNotificationMessage(
                locale: $user->getLocale() ?? $this->locale,
                subject: 'subject.document_create',
                to: $user->getEmail(),
                from: $this->sender,
                template: 'document_create',
                templateParameters: $templateParameters
            );

            $this->addEmailNotificationToQueue($emailNotificationToQueueData);
        }
    }
}
