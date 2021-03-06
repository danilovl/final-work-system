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

namespace App\Widget;

use App\Service\{
    UserService,
    TranslatorService
};
use Danilovl\ParameterBundle\Services\ParameterService;
use Twig\Environment;

class UserProfileInformationNotifyWidget extends BaseWidget
{
    public function __construct(
        protected UserService $userService,
        protected ParameterService $parameterService,
        protected TranslatorService $translatorService,
        protected Environment $twig
    ) {
    }

    public function render(): ?string
    {
        $result = null;
        foreach (['checkPhone', 'checkSkype'] as $method) {
            $result .= $this->{$method}();
        }

        return $result;
    }

    private function checkPhone(): ?string
    {
        $user = $this->userService->getUser();
        if ($user->getPhone() !== null) {
            return null;
        }

        return $this->twig->render('widget/notify.html.twig', [
            'class' => $this->parameterService->get('homepage_notify.type_class.info'),
            'message' => $this->translatorService->trans('app.text.please_fill_phone_number_before_use_app')
        ]);
    }

    private function checkSkype(): ?string
    {
        $user = $this->userService->getUser();
        if ($user->getSkype() !== null) {
            return null;
        }

        return $this->twig->render('widget/notify.html.twig', [
            'class' => $this->parameterService->get('homepage_notify.type_class.info'),
            'message' => $this->translatorService->trans('app.text.please_fill_skype_before_use_app')
        ]);
    }
}
