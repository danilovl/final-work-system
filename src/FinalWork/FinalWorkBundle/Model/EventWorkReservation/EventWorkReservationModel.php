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

namespace FinalWork\FinalWorkBundle\Model\EventWorkReservation;

use Symfony\Component\Validator\Constraints as Assert;

class EventWorkReservationModel
{
    /**
     * @Assert\NotBlank()
     */
    public $work;
}
