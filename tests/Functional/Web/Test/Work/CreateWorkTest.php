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

namespace App\Tests\Functional\Web\Test\Work;

use App\Tests\Functional\Web\Enum\LoginData;
use App\Tests\Functional\Web\Traits\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class CreateWorkTest extends WebTestCase
{
    use LoginTrait;

    public function testCreateWorkSuccess(): void
    {
        $client = static::createClient();

        $this->login(
            client: $client,
            username: LoginData::SUPERVISOR_USERNAME->value,
            password: LoginData::SUPERVISOR_PASSWORD->value
        );

        $crawler = $client->request(Request::METHOD_GET, '/en/work/create');

        $workTitle = 'New work title ' . rand(1, 100);

        $form = $crawler->filter('#work-button-action')->form();
        $form['work[title]'] = $workTitle;
        $form['work[shortcut]'] = 'test shortcut';
        $form['work[deadline]'] = '2022-01-01';
        $client->submit($form);

        $crawler = $client->followRedirect();
        $pageTitle = $crawler->filterXPath('//title')->text();

        $this->assertEquals($workTitle, $pageTitle);
    }
}
