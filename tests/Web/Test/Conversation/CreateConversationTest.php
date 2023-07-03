<?php declare(strict_types=1);

namespace App\Tests\Web\Test\Conversation;

use App\Tests\Web\Enum\LoginData;
use App\Tests\Web\Traits\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class CreateConversationTest extends WebTestCase
{
    use LoginTrait;

    public function testCreateConversationSuccess(): void
    {
        $client = static::createClient();

        $this->login(
            $client,
            LoginData::SUPERVISOR_USERNAME->value,
            LoginData::SUPERVISOR_PASSWORD->value
        );

        $crawler = $client->request(Request::METHOD_GET, '/en/conversation/create');

        $form = $crawler->filter('#btn-conversation-create')->form();
        $values = $form['conversation_compose_message[conversation]']->availableOptionValues();

        $form['conversation_compose_message[name]'] = 'New conversation title';
        $form['conversation_compose_message[conversation]']->select($values[1]);
        $form['conversation_compose_message[content]'] = 'Message';

        $client->submit($form);
        $this->assertResponseRedirects('/en/conversation/list');
    }
}
