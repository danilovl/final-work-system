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

use App\Application\Service\{
    TranslatorService,
    TwigRenderService
};
use App\Domain\EmailNotification\Entity\EmailNotification;
use App\Domain\EmailNotification\Factory\EmailNotificationFactory;
use App\Domain\User\Facade\UserFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Danilovl\ParameterBundle\Service\ParameterService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\{
    Envelope,
    MessageBusInterface
};

abstract class AbstractBaseEmailNotificationSubscriber extends TestCase
{
    protected static string $classSubscriber;

    protected EventDispatcher $dispatcher;

    protected readonly UserFacade $userFacade;

    protected readonly TwigRenderService $twigRenderService;

    protected readonly TranslatorService $translator;

    protected readonly EmailNotificationFactory $emailNotificationFactory;

    protected readonly ParameterServiceInterface $parameterService;

    protected readonly MessageBusInterface $bus;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher;

        $this->userFacade = $this->createMock(UserFacade::class);
        $this->twigRenderService = $this->createMock(TwigRenderService::class);
        $this->translator = $this->createMock(TranslatorService::class);
        $this->translator
            ->expects($this->any())
            ->method('trans')
            ->willReturn('trans');

        $this->emailNotificationFactory = $this->createMock(EmailNotificationFactory::class);

        $this->emailNotificationFactory
            ->expects($this->any())
            ->method('createFromModel')
            ->willReturn(new EmailNotification);

        $this->bus = $this->createMock(MessageBusInterface::class);
        $envelope = new Envelope(new stdClass);

        $this->bus
            ->expects($this->any())
            ->method('dispatch')
            ->willReturn($envelope);

        $parameterBug = new ParameterBag([
            'email_notification' => [
                'sender' => 'test@example.com',
                'default_locale' => 'en',
                'sure_exist_template_locale' => 'en',
                'translator_domain' => 'email_notification',
                'enable_add_to_queue' => true,
                'enable_messenger' => true
            ]
        ]);
        $this->parameterService = new ParameterService($parameterBug);
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

    public function testCountEvent(): void
    {
        $this->assertCount(
            count($this->subscriber::getSubscribedEvents()),
            $this->subscriber::getSubscribedEvents()
        );
    }

    public static function subscribedEvents(): Generator
    {
        foreach ((static::$classSubscriber)::getSubscribedEvents() as $eventKey => $event) {
            yield [$eventKey, $event];
        }
    }
}
