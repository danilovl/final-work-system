import { Controller } from '@hotwired/stimulus';
import { conversationEventSource } from '../js/main.js';

/**
 * Prepends new conversation messages published to Mercure.
 *
 *   <div id="chat" data-controller="conversation-stream" data-conversation-stream-url-value="{{ mercure(topic) }}">
 */
export default class extends Controller {
    static values = {
        url: String
    };

    connect() {
        this.eventSource = conversationEventSource(this.urlValue, $(this.element));
    }

    disconnect() {
        this.eventSource.close();
    }
}
