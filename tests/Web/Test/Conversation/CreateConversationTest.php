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

namespace App\Tests\Web\Test\Conversation;

use App\Tests\Web\Enum\LoginData;
use App\Tests\Web\Traits\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
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
        /** @var ChoiceFormField $choiceFormField */
        $choiceFormField = $form['conversation_compose_message[conversation]'];
        $values = $choiceFormField->availableOptionValues();

        $form['conversation_compose_message[name]'] = 'New conversation title';
        /** @var ChoiceFormField $conversationField */
        $conversationField = $form['conversation_compose_message[conversation]'];
        $conversationField->select($values[0]);
        $form['conversation_compose_message[content]'] = 'Message';

        $client->submit($form);
        $this->assertResponseRedirects('/en/conversation/list');
    }
}
