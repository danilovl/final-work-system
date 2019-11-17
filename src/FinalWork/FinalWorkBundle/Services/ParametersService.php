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

namespace FinalWork\FinalWorkBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ParametersService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ParametersService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getParam(string $key)
    {
        $keys = explode('.', $key);
        $configs = $this->container->getParameter($keys[0]);
        array_shift($keys);

        if (empty($keys)) {
            return $configs;
        }

        foreach ($keys as $key) {
            if (isset($configs[$key])) {
                $configs = $configs[$key];
            } else {
                return null;
            }
        }

        return $configs;
    }
}
