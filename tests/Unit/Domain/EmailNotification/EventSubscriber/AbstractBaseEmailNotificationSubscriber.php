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

namespace App\Tests\Unit\Domain\EmailNotification\EventSubscriber;

use App\Domain\EmailNotification\Entity\EmailNotification;
use App\Domain\EmailNotification\Factory\EmailNotificationFactory;
use App\Domain\EmailNotification\Provider\{
    EmailNotificationAddToQueueProvider,
    EmailNotificationEnableMessengerProvider
};
use App\Domain\User\Facade\UserFacade;
use App\Infrastructure\Service\{
    TranslatorService
};
use App\Infrastructure\Service\TwigRenderService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Danilovl\ParameterBundle\Service\ParameterService;
use Generator;
use PHPUnit\Framework\Attributes\{
    AllowMockObjectsWithoutExpectations,
    DataProvider
};
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\{
    EventDispatcher,
    EventSubscriberInterface
};
use Symfony\Component\Messenger\{
    Envelope,
    MessageBusInterface
};

#[AllowMockObjectsWithoutExpectations]
abstract class AbstractBaseEmailNotificationSubscriber extends TestCase
{
    protected static string $classSubscriber;

    protected EventDispatcher $dispatcher;

    protected MockObject&UserFacade $userFacade;

    protected TwigRenderService $twigRenderService;

    protected TranslatorService $translator;

    protected MockObject&EmailNotificationFactory $emailNotificationFactory;

    protected EmailNotificationAddToQueueProvider $emailNotificationAddToQueueProvider;

    protected EmailNotificationEnableMessengerProvider $emailNotificationEnableMessengerProvider;

    protected ParameterServiceInterface $parameterService;

    protected MockObject&MessageBusInterface $bus;

    protected EventSubscriberInterface $subscriber;

    protected bool $isEmailNotificationAddToQueueProvider;

    protected bool $isEmailNotificationEnableMessengerProvider;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher;

        $this->userFacade = $this->createMock(UserFacade::class);
        $this->twigRenderService = $this->createStub(TwigRenderService::class);
        $this->translator = $this->createStub(TranslatorService::class);
        $this->translator
            ->method('trans')
            ->willReturn('trans');

        $this->emailNotificationFactory = $this->createMock(EmailNotificationFactory::class);

        $this->emailNotificationFactory
            ->method('createFromModel')
            ->willReturn(new EmailNotification);

        $this->bus = $this->createMock(MessageBusInterface::class);
        $envelope = new Envelope(new stdClass);

        $this->bus
            ->method('dispatch')
            ->willReturn($envelope);

        $parameterBug = new ParameterBag([
            'email_notification' => [
                'sender' => 'test@example.com',
                'default_locale' => 'en',
                'sure_exist_template_locale' => 'en',
                'translator_domain' => 'email_notification'
            ]
        ]);
        $this->parameterService = new ParameterService($parameterBug);

        $this->isEmailNotificationAddToQueueProvider = true;
        $this->isEmailNotificationEnableMessengerProvider = true;

        $this->emailNotificationAddToQueueProvider = $this->createStub(EmailNotificationAddToQueueProvider::class);
        $this->emailNotificationAddToQueueProvider
            ->method('isEnable')
            ->willReturnCallback(function (): bool {
                return $this->isEmailNotificationAddToQueueProvider;
            });

        $this->emailNotificationEnableMessengerProvider = $this->createStub(EmailNotificationEnableMessengerProvider::class);
        $this->emailNotificationEnableMessengerProvider
            ->method('isEnable')
            ->willReturnCallback(function (): bool {
                return $this->isEmailNotificationEnableMessengerProvider;
            });
    }

    #[DataProvider('subscribedEvents')]
    public function testInitialState(string $eventKey): void
    {
        $this->assertEquals([], $this->dispatcher->getListeners());
        $this->assertFalse($this->dispatcher->hasListeners($eventKey));
    }

    #[DataProvider('subscribedEvents')]
    public function testAddSubscriber(string $eventKey): void
    {
        $this->dispatcher->addSubscriber($this->subscriber);
        $this->assertTrue($this->dispatcher->hasListeners($eventKey));
    }

    #[DataProvider('subscribedEvents')]
    public function testRemoveSubscriber(string $eventKey): void
    {
        $this->dispatcher->addSubscriber($this->subscriber);
        $this->assertTrue($this->dispatcher->hasListeners($eventKey));
        $this->dispatcher->removeSubscriber($this->subscriber);
        $this->assertFalse($this->dispatcher->hasListeners($eventKey));
    }

    #[DataProvider('subscribedEvents')]
    public function testCountEvent(string $eventKey): void
    {
        $this->assertCount(
            count($this->subscriber::getSubscribedEvents()),
            $this->subscriber::getSubscribedEvents()
        );
    }

    public static function subscribedEvents(): Generator
    {
        foreach ((static::$classSubscriber)::getSubscribedEvents() as $eventKey => $event) {
            yield [$eventKey];
        }
    }
}
