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

namespace App\Domain\User\EventListener;

use App\Domain\User\Service\UserService;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UnexpectedValueException;
use function Symfony\Component\String\u;

class RedirectToPreferredLocaleListener implements EventSubscriberInterface
{
    private array $locales;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserService $userService,
        string $locales,
        private ?string $defaultLocale = null
    ) {
        $this->locales = explode('|', mb_trim($locales));

        if (empty($this->locales[0])) {
            throw new UnexpectedValueException('The list of supported locales must not be empty.');
        }

        $this->defaultLocale = $defaultLocale ?: $this->locales[0];
        if (!in_array($this->defaultLocale, $this->locales, true)) {
            throw new UnexpectedValueException(sprintf('The default locale ("%s") must be one of "%s".', $this->defaultLocale, $locales));
        }

        array_unshift($this->locales, $this->defaultLocale);
        $this->locales = array_unique($this->locales);
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || '/' !== $request->getPathInfo()) {
            return;
        }

        $referrer = $request->headers->get('referer');
        if ($referrer !== null && u($referrer)->ignoreCase()->startsWith($request->getSchemeAndHttpHost())) {
            return;
        }

        $preferredLanguage = $request->getPreferredLanguage($this->locales);
        $user = $this->userService->getUserOrNull();

        if ($user !== null && $user->getLocale()) {
            $preferredLanguage = $user->getLocale();
        }

        if ($preferredLanguage !== $this->defaultLocale) {
            $response = new RedirectResponse($this->urlGenerator->generate('homepage', ['_locale' => $preferredLanguage]));
            $event->setResponse($response);
        }
    }
}
