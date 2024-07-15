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

namespace App\Application\Middleware\EventCalendar\Ajax;

use Symfony\Component\HttpKernel\Event\ControllerEvent;

class GetEventMiddleware extends EditMiddleware
{
    public function __invoke(ControllerEvent $event): bool
    {
        $request = $event->getRequest();
        /** @var string|null $type */
        $type = $request->attributes->get('type');

        if (empty($type)) {
            $this->setResponse($event);

            return true;
        }

        return parent::__invoke($event);
    }
}
