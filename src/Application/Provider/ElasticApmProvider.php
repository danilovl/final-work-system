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

namespace App\Application\Provider;

use App\Application\Service\IniService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;

readonly class ElasticApmProvider
{
    public function __construct(
        private ParameterServiceInterface $parameterService,
        private IniService $iniService
    ) {}

    public function isEnable(): bool
    {
        $isEnableEnv = $this->parameterService->getBoolean('apm.enable');
        if (!$isEnableEnv) {
            return false;
        }

        $isEnableIni = $this->iniService->get('elastic_apm.enabled');

        return !empty($isEnableIni);
    }
}
