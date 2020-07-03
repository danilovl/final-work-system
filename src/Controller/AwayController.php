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

namespace App\Controller;

use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class AwayController extends BaseController
{
    public function to(Request $request): Response
    {
        $this->get('app.seo_page')->setTitle('app.page.leave_web');

        return $this->render('away/to.html.twig', [
            'url' => $request->get('url')
        ]);
    }
}