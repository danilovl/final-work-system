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

namespace App\EventDispatcher\GenericEvent;

class WidgetGenericGenericEvent
{
    public string $groupName;
    public array $groupWidgets;

    public function __construct(string $groupName, array $groupWidgets)
    {
        $this->groupName = $groupName;
        $this->groupWidgets = $groupWidgets;
    }
}