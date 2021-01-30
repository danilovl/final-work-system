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

namespace App\Helper;

use App\Entity\SystemEventRecipient;

class SystemEventHelper
{
    public static function groupSystemEventByType(iterable $events): array
    {
        $systemEventGroup = [];

        /** @var SystemEventRecipient $recipient */
        foreach ($events as $recipient) {
            $systemEventType = $recipient->getSystemEvent()->getType()->getGroup();

            if (isset($systemEventGroup[$systemEventType])) {
                $systemEventGroup[$systemEventType][] = $recipient;
            } else {
                $systemEventGroup[$systemEventType] = [];
                $systemEventGroup[$systemEventType][] = $recipient;
            }
        }

        return $systemEventGroup;
    }
}
