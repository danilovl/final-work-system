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

namespace App\Tests\Functional\Web\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

trait LoginTrait
{
    public function login(KernelBrowser $client, string $username, string $password): Crawler
    {
        $crawler = $client->request(Request::METHOD_GET, '/en/login');

        $form = $crawler->filter('#_submit')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;

        $client->submit($form);
        $client->followRedirect();

        return $crawler;
    }
}
