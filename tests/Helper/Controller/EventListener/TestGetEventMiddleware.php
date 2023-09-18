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

namespace App\Tests\Helper\Controller\EventListener;

use App\Application\Interfaces\Middleware\RequestMiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;

class TestGetEventMiddleware implements RequestMiddlewareInterface
{
    public static function handle(Request $request): bool
    {
        return true;
    }
}
