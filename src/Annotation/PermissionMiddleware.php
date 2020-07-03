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

namespace App\Annotation;

use DateTime;

/**
 * @Annotation
 */
class PermissionMiddleware
{
    public ?array $roles = null;
    public ?array $users = null;
    public ?DateTime $dateFrom = null;
    public ?DateTime $dateTo = null;

    public function __construct(array $options)
    {
        $this->roles = $options['roles'] ?? null;
        $this->users = $options['users'] ?? null;

        $dateFrom = $options['date_from'] ?? null;
        if ($dateFrom !== null) {
            $this->dateFrom = new DateTime($dateFrom);
        }

        $dateTo = $options['date_to'] ?? null;
        if ($dateTo !== null) {
            $this->dateTo = new DateTime($dateTo);
        }
    }
}