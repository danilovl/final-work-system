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

namespace App\EventListener\EmailNotification;

use App\Model\EmailNotificationQueue\EmailNotificationQueueModel;
use App\Model\User\UserFacade;
use Danilovl\ParameterBundle\Services\ParameterService;
use App\Model\EmailNotificationQueue\EmailNotificationQueueFactory;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class BaseEmailNotificationSubscriber
{
    private UserFacade $userFacade;
    private ParameterService $parameterService;
    protected EmailNotificationQueueFactory $emailNotificationQueueFactory;
    protected Environment $twig;
    protected TranslatorInterface $translator;

    protected ?string $sender;
    protected string $locale;
    protected ?string $translatorDomain;
    protected bool $enableAddToQueue;

    public function __construct(
        UserFacade $userFacade,
        Environment $twig,
        TranslatorInterface $translator,
        EmailNotificationQueueFactory $emailNotificationQueueFactory,
        ParameterService $parameterService
    ) {
        $this->userFacade = $userFacade;
        $this->emailNotificationQueueFactory = $emailNotificationQueueFactory;
        $this->twig = $twig;
        $this->parameterService = $parameterService;
        $this->translator = clone $translator;

        $this->initParameters();
        $this->initTranslatorLocale($this->locale);
    }

    private function initParameters(): void
    {
        $this->sender = $this->parameterService->get('email_notification.sender');
        $this->locale = $this->parameterService->get('email_notification.default_locale');
        $this->translatorDomain = $this->parameterService->get('email_notification.translator_domain');
        $this->enableAddToQueue = $this->parameterService->get('email_notification.enable_add_to_queue');
    }

    protected function initTranslatorLocale(string $locale): void
    {
        $this->translator->setLocale($locale);
    }

    protected function getTemplate(string $name): string
    {
        return "email_notification/{$this->locale}/{$name}.html.twig";
    }

    protected function trans(string $trans): string
    {
        return $this->translator->trans("app.email_notification.{$trans}", [], $this->translatorDomain);
    }

    protected function addEmailNotificationToQueue(
        string $subject,
        string $to,
        string $from,
        string $body
    ): void {
        if (!$this->enableAddToQueue) {
            return;
        }

        $userTo = $this->userFacade->findUserByEmail($to);
        if ($userTo && !$userTo->isEnabledEmailNotification()) {
            return;
        }

        $emailNotificationQueueModel = new EmailNotificationQueueModel;
        $emailNotificationQueueModel->subject = $subject;
        $emailNotificationQueueModel->to = $to;
        $emailNotificationQueueModel->from = $from;
        $emailNotificationQueueModel->body = $body;

        $this->emailNotificationQueueFactory->createFromModel($emailNotificationQueueModel);
    }
}