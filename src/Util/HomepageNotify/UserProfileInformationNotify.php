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

namespace App\Util\HomepageNotify;

use App\Interfaces\HomepageNotifyInterface;

class UserProfileInformationNotify extends BaseNotify implements HomepageNotifyInterface
{
    public function renderNotify(): ?string
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

        return $this->twig->render('homepage_notify/notify.html.twig', [
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

        return $this->twig->render('homepage_notify/notify.html.twig', [
            'class' => $this->parameterService->get('homepage_notify.type_class.info'),
            'message' => $this->translatorService->trans('app.text.please_fill_skype_before_use_app')
        ]);
    }
}
