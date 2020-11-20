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

namespace App\Utils\HomepageNotify;

use Danilovl\ParameterBundle\Services\ParameterService;
use App\Services\{
    UserService,
    TranslatorService
};
use Twig\Environment;

class BaseNotify
{
    protected UserService $userService;
    protected ParameterService $parameterService;
    protected Environment $twig;
    protected TranslatorService $translatorService;

    public function __construct(
        UserService $userService,
        ParameterService $parameterService,
        TranslatorService $translatorService,
        Environment $twig
    ) {
        $this->userService = $userService;
        $this->translatorService = $translatorService;
        $this->twig = $twig;
        $this->parameterService = $parameterService;
    }
}