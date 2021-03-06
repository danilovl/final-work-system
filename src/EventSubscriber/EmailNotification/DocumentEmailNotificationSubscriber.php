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

namespace App\EventSubscriber\EmailNotification;

use App\Constant\WorkUserTypeConstant;
use App\EventDispatcher\GenericEvent\MediaGenericEvent;
use App\Entity\User;
use App\EventSubscriber\Events;
use App\Model\EmailNotificationQueue\EmailNotificationQueueFactory;
use App\Model\User\UserFacade;
use App\Service\UserWorkService;
use Danilovl\ParameterBundle\Services\ParameterService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class DocumentEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected UserFacade $userFacade,
        protected Environment $twig,
        protected TranslatorInterface $translator,
        protected EmailNotificationQueueFactory $emailNotificationQueueFactory,
        protected ParameterService $parameterService,
        private UserWorkService $userWorkService
    ) {
        parent::__construct(
            $userFacade,
            $twig,
            $translator,
            $emailNotificationQueueFactory,
            $parameterService
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_DOCUMENT_CREATE => 'onDocumentCreate'
        ];
    }

    public function onDocumentCreate(MediaGenericEvent $event): void
    {
        $media = $event->media;
        $owner = $media->getOwner();

        $subject = $this->trans('subject.document_create');
        $body = $this->twig->render($this->getTemplate('document_create'), [
            'media' => $media
        ]);
        $recipientArray = $this->userWorkService->getActiveAuthor($owner, WorkUserTypeConstant::SUPERVISOR);

        /** @var User $user */
        foreach ($recipientArray as $user) {
            $this->addEmailNotificationToQueue($subject, $user->getEmail(), $this->sender, $body);
        }
    }
}