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

namespace App\Domain\SystemEventRecipient\Bus\Query\UnreadSystemEventsByRecipient;

use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;

readonly class GetUnreadSystemEventsByRecipientQueryResult
{
    /**
     * @param SystemEventRecipient[] $systemEventRecipients
     */
    public function __construct(
        public array $systemEventRecipients
    ) {}
}
