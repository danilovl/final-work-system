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

namespace App\Infrastructure\Web\Twig\Runtime;

use App\Infrastructure\Service\{
    RequestService,
    TranslatorService
};
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

class FrontendConfigRuntime
{
    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

    public function __construct(
        private readonly RequestService $requestService,
        private readonly TranslatorService $translatorService
    ) {}

    #[AsTwigFunction('frontend_config', ['is_safe' => ['html']])]
    public function config(): string
    {
        $config = [
            'messages' => [
                'error403' => $this->translatorService->trans('app.flash.error.403'),
                'error404' => $this->translatorService->trans('app.flash.error.404'),
                'error500' => $this->translatorService->trans('app.flash.error.500'),
                'networkError' => $this->translatorService->trans('app.flash.error.network'),
                'sureWantClose' => $this->translatorService->trans('app.form.validation.sure_want_close')
            ],
            'flashes' => $this->flashes()
        ];

        return json_encode($config, self::JSON_FLAGS);
    }

    /**
     * @return array<int, array{type: string, message: string}>
     */
    private function flashes(): array
    {
        $session = $this->requestService->getSession();
        if (!$session instanceof FlashBagAwareSessionInterface) {
            return [];
        }

        $flashes = [];
        foreach ($session->getFlashBag()->all() as $type => $messages) {
            foreach ($messages as $message) {
                $flashes[] = [
                    'type' => (string) $type,
                    'message' => (string) $message
                ];
            }
        }

        return $flashes;
    }
}
