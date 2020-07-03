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

namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class FirstWeekDay extends Constraint
{
    public string $message = 'It is not first day of the week';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
