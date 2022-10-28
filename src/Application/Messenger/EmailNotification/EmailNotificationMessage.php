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

namespace App\Application\DataTransferObject\EventSubscriber;

use App\Application\DataTransferObject\BaseDataTransferObject;

class EmailNotificationToQueueData extends BaseDataTransferObject
{
    public string $locale;
    public string $subject;
    public string $to;
    public string $from;
    public string $template;
    public array $templateParameters = [];
}
