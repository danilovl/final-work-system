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

namespace App\Tests\Functional\Web\Test\Task;

use App\Tests\Functional\Web\Enum\LoginData;
use App\Tests\Functional\Web\Traits\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class CreateTaskTest extends WebTestCase
{
    use LoginTrait;

    public function testCreateTaskSuccess(): void
    {
        $client = static::createClient();

        $this->login(
            client: $client,
            username: LoginData::SUPERVISOR_USERNAME->value,
            password: LoginData::SUPERVISOR_PASSWORD->value
        );

        $crawler = $client->request(Request::METHOD_GET, '/en/work/supervisor/list');

        $link = $crawler->filter('.btn.btn-primary.btn-xs')->first()->link();
        $crawler = $client->click($link);

        $link = $crawler->filter('#task-create')->first()->link();
        preg_match('~/work/([a-zA-Z0-9]+)/task/~', $link->getUri(), $matches);
        /** @var array{0: string, 1: string} $matches */
        $workId = $matches[1];

        $crawler = $client->click($link);

        $form = $crawler->filter('#task-button-action')->form();
        $form['task[name]'] = 'Task title ' . rand(1, 100);
        $form['task[deadline]'] = '2023-07-02';
        $form['task[description]'] = 'Task description ' . rand(1, 100);
        $client->submit($form);

        $this->assertResponseRedirects('/en/work/detail/' . $workId);
    }
}
