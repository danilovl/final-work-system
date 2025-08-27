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

namespace App\Application\Interfaces\Bus;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): Envelope;

    /**
     * @template T of object
     * @param CommandInterface $command
     * @return object
     * @throws ExceptionInterface
     */
    public function dispatchResult(CommandInterface $command): object;
}
