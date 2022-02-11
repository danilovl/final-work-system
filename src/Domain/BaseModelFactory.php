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

namespace App\Domain;

use App\Application\Service\EntityManagerService;

class BaseModelFactory
{
    public function __construct(protected EntityManagerService $entityManagerService)
    {
    }
}