import { Controller } from '@hotwired/stimulus';

/**
 * Replaces a top navigation widget with the content published to Mercure.
 *
 *   <div hidden data-controller="mercure-widget"
 *        data-mercure-widget-url-value="{{ mercure(topic) }}"
 *        data-mercure-widget-element-id-value="top-nav-unread-system-events"></div>
 */
export default class extends Controller {
    static values = {
        url: String,
        elementId: String
    };

    connect() {
        this.eventSource = widgetEventSource(this.urlValue, this.elementIdValue);
    }

    disconnect() {
        this.eventSource.close();
    }
}
