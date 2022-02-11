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

namespace App\Application\EventSubscriber\EmailNotification;

use App\Application\DataTransferObject\EventSubscriber\EmailNotificationToQueueData;
use App\Application\Service\TranslatorService;
use App\Domain\EmailNotificationQueue\EmailNotificationQueueModel;
use App\Domain\EmailNotificationQueue\Factory\EmailNotificationQueueFactory;
use App\Domain\User\Facade\UserFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Twig\Environment;

class BaseEmailNotificationSubscriber
{
    protected string $sender;
    protected string $locale;
    private string $sureExistTemplateLocale;
    protected string $translatorDomain;
    protected bool $enableAddToQueue;
    protected bool $enableRabbitMq;

    public function __construct(
        protected UserFacade $userFacade,
        protected Environment $twig,
        protected TranslatorService $translator,
        protected EmailNotificationQueueFactory $emailNotificationQueueFactory,
        protected ParameterServiceInterface $parameterService,
        protected ProducerInterface $emailNotificationProducer
    ) {
        $this->translator = clone $translator;

        $this->initParameters();
        $this->initTranslatorLocale($this->locale);
    }

    private function initParameters(): void
    {
        $this->sender = $this->parameterService->getString('email_notification.sender');
        $this->locale = $this->parameterService->getString('email_notification.default_locale');
        $this->sureExistTemplateLocale = $this->parameterService->getString('email_notification.sure_exist_template_locale');
        $this->translatorDomain = $this->parameterService->getString('email_notification.translator_domain');
        $this->enableAddToQueue = $this->parameterService->getBoolean('email_notification.enable_add_to_queue');
        $this->enableRabbitMq = $this->parameterService->getBoolean('email_notification.enable_rabbit_mq');
    }

    protected function initTranslatorLocale(string $locale): void
    {
        $this->translator->setLocale($locale);
    }

    protected function getTemplate(string $locale, string $name): string
    {
        return "email_notification/{$locale}/{$name}.html.twig";
    }

    protected function trans(string $trans): string
    {
        return $this->translator->trans("app.email_notification.{$trans}", [], $this->translatorDomain);
    }

    protected function addEmailNotificationToQueue(EmailNotificationToQueueData $queueData): void
    {
        if (!$this->enableAddToQueue) {
            return;
        }

        $to = $queueData->to;
        $userTo = $this->userFacade->findOneByEmail($to);

        if ($userTo && !$userTo->isEnabledEmailNotification()) {
            return;
        }

        if ($this->enableRabbitMq) {
            $this->sendToRabbitQueue($queueData);

            return;
        }

        $this->saveLocal($queueData);
    }

    public function renderBody(
        string $locale,
        string $template,
        array $templateParameters = []
    ): string {
        $templatePath = $this->getTemplate($locale, $template);
        if (!$this->twig->getLoader()->exists($templatePath)) {
            $locale = $this->sureExistTemplateLocale;
            $this->initTranslatorLocale($locale);
        }

        return $this->twig->render(
            $this->getTemplate($locale, $template),
            $templateParameters
        );
    }

    protected function saveLocal(EmailNotificationToQueueData $queueData): void
    {
        $body = $this->renderBody(
            $queueData->locale,
            $queueData->template,
            $queueData->templateParameters
        );

        $emailNotificationQueueModel = new EmailNotificationQueueModel;
        $emailNotificationQueueModel->subject = $queueData->subject;
        $emailNotificationQueueModel->to = $queueData->to;
        $emailNotificationQueueModel->from = $queueData->from;
        $emailNotificationQueueModel->body = $body;

        $this->emailNotificationQueueFactory->createFromModel($emailNotificationQueueModel);
    }

    protected function sendToRabbitQueue(EmailNotificationToQueueData $queueData): void
    {
        $this->emailNotificationProducer->publish($queueData->toJson());
    }
}
