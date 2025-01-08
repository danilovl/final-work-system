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

namespace App\Application\Collector;

use Override;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

class SystemInfoCollector extends DataCollector
{
    public const string NAME_COLLECTOR = 'final_work_info';

    public function __construct(private readonly array $systemInfo) {}

    #[Override]
    public function collect(
        Request $request,
        Response $response,
        Throwable $exception = null
    ): void {
        $this->data = [
            'system_info' => $this->systemInfo
        ];
    }

    #[Override]
    public function reset(): void
    {
        $this->data = [];
    }

    #[Override]
    public function getName(): string
    {
        return self::NAME_COLLECTOR;
    }

    public function getSystemName(): string
    {
        return $this->data['system_info']['name'];
    }

    public function getVersion(): string
    {
        return $this->data['system_info']['version'];
    }
}
