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

namespace FinalWork\FinalWorkBundle\Controller\Middleware\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface RequestMiddlewareInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public static function handle(Request $request): bool;
}