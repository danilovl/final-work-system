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

namespace FinalWork\FinalWorkBundle\Collector;

use Exception;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class SystemInfoCollector extends DataCollector
{
    /**
     * @var string
     */
    private $systemInfo;

    /**
     * SystemInfoCollector constructor.
     * @param array $systemInfo
     */
    public function __construct(array $systemInfo)
    {
        $this->systemInfo = $systemInfo;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Exception|null $exception
     */
    public function collect(
        Request $request,
        Response $response,
        Exception $exception = null
    ) {
        $this->data = [
            'system_info' => $this->systemInfo
        ];
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'final_work_info';
    }

    /**
     * @return string
     */
    public function getSystemName(): string
    {
        return $this->data['system_info']['name'];
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->data['system_info']['version'];
    }
}