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

namespace App\Domain\ConversationMessage\EventSubscriber;

use App\Application\Traits\Repository\Elastica\ElasticaSearchTrait;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use FOS\ElasticaBundle\Event\PostTransformEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class ConversationMessageSearchContentSubscriber implements EventSubscriberInterface
{
    use ElasticaSearchTrait;

    public const string FIELD = 'content_search';

    public static function getSubscribedEvents(): array
    {
        return [
            PostTransformEvent::class => 'onPostTransform'
        ];
    }

    public function onPostTransform(PostTransformEvent $event): void
    {
        $conversationMessage = $event->getObject();
        if (!$conversationMessage instanceof ConversationMessage) {
            return;
        }

        $content = $conversationMessage->getContent();
        $searchContent = $this->createSearchContent($content);

        $event->getDocument()->set(self::FIELD, $searchContent);
    }

    public function createSearchContent(string $content): string
    {
        $text = preg_replace('~<[^>]*>~', ' ', $content) ?? $content;
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('~\s+~u', ' ', $text) ?? $text;
        $text = mb_trim($text);

        return $this->transformSearch($text);
    }
}
