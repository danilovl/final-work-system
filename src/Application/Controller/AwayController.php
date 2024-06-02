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

namespace App\Application\Controller;

use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AwayController extends AbstractController
{
    public function to(Request $request): Response
    {
        return $this->render('application/away/to.html.twig', [
            'url' => $request->query->get('url')
        ]);
    }
}