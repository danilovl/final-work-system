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

use App\Application\Attribute\AjaxRequestMiddlewareAttribute;
use Symfony\Component\HttpFoundation\Response;

#[AjaxRequestMiddlewareAttribute(class: TestGetEventMiddleware::class)]
class TestController
{
    #[AjaxRequestMiddlewareAttribute(class: TestGetEventMiddleware::class)]
    public function index(): Response
    {
        return new Response('content');
    }

    #[AjaxRequestMiddlewareAttribute(class: TestGetEventMiddlewareFailed::class)]
    public function error(): Response
    {
        return new Response('content');
    }
}
