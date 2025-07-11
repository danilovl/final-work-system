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

use App\Domain\EmailNotification\Provider\{
    EmailNotificationAddToQueueProvider,
    EmailNotificationEnableMessengerProvider
};
use App\Infrastructure\Service\{
    TranslatorService,
    TwigRenderService
};
use App\Domain\EmailNotification\Factory\EmailNotificationFactory;
use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use App\Domain\EmailNotification\Model\EmailNotificationModel;
use App\Domain\User\Facade\UserFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class BaseEmailNotificationSubscriber
{
    protected string $sender;

    protected string $locale;

    private string $sureExistTemplateLocale;

    protected string $translatorDomain;

    public function __construct(
        protected UserFacade $userFacade,
        protected TwigRenderService $twigRenderService,
        protected TranslatorService $translator,
        protected EmailNotificationFactory $emailNotificationFactory,
        protected ParameterServiceInterface $parameterService,
        protected MessageBusInterface $bus,
        protected EmailNotificationAddToQueueProvider $emailNotificationAddToQueueProvider,
        protected EmailNotificationEnableMessengerProvider $emailNotificationEnableMessengerProvider
    ) {
        $this->initParameters();
    }

    private function initParameters(): void
    {
        $this->sender = $this->parameterService->getString('email_notification.sender');
        $this->locale = $this->parameterService->getString('email_notification.default_locale');
        $this->sureExistTemplateLocale = $this->parameterService->getString('email_notification.sure_exist_template_locale');
        $this->translatorDomain = $this->parameterService->getString('email_notification.translator_domain');
    }

    protected function getTemplate(string $locale, string $name): string
    {
        return "application/email_notification/{$locale}/{$name}.html.twig";
    }

    public function trans(string $trans, string $locale): string
    {
        return $this->translator->trans("app.email_notification.{$trans}", [], $this->translatorDomain, $locale);
    }

    public function addEmailNotificationToQueue(EmailNotificationMessage $emailNotificationMessage): void
    {
        if (!$this->emailNotificationAddToQueueProvider->isEnable()) {
            return;
        }

        $emailNotificationMessage->generateUuid();
        $to = $emailNotificationMessage->to;
        $userTo = $this->userFacade->findByEmail($to);

        if ($userTo && !$userTo->isEnabledEmailNotification()) {
            return;
        }

        if ($this->emailNotificationEnableMessengerProvider->isEnable()) {
            $this->sendToMessenger($emailNotificationMessage);

            return;
        }

        $this->saveLocal($emailNotificationMessage);
    }

    public function renderBody(
        string $locale,
        string $template,
        array $templateParameters = []
    ): string {
        $templatePath = $this->getTemplate($locale, $template);
        if (!$this->twigRenderService->getLoader()->exists($templatePath)) {
            $locale = $this->sureExistTemplateLocale;
        }

        return $this->twigRenderService->render(
            $this->getTemplate($locale, $template),
            $templateParameters
        );
    }

    protected function saveLocal(EmailNotificationMessage $message): void
    {
        $subject = $this->trans($message->subject, $message->locale);

        $body = $this->renderBody(
            $message->locale,
            $message->template,
            $message->templateParameters
        );

        $emailNotificationModel = new EmailNotificationModel;
        $emailNotificationModel->subject = $subject;
        $emailNotificationModel->to = $message->to;
        $emailNotificationModel->from = $message->from;
        $emailNotificationModel->body = $body;
        $emailNotificationModel->uuid = $message->uuid;

        $this->emailNotificationFactory->createFromModel($emailNotificationModel);
    }

    protected function sendToMessenger(EmailNotificationMessage $queueData): void
    {
        $this->bus->dispatch($queueData);
    }
}
