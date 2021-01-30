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

use Danilovl\ParameterBundle\Services\ParameterService;
use App\Service\{
    UserService,
    TranslatorService
};
use Twig\Environment;

class BaseNotify
{
    public function __construct(
        protected UserService $userService,
        protected ParameterService $parameterService,
        protected TranslatorService $translatorService,
        protected Environment $twig
    ) {
    }
}
