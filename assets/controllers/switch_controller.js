import { Controller } from '@hotwired/stimulus';
import { notifyMessage } from '../js/main.js';

/**
 * Toggle switch for a checkbox. The look comes from the "js-switch" CSS class, the element is not wrapped.
 *
 *   <input type="checkbox" class="js-switch" data-controller="switch">
 *
 * With data-target-url the new state is sent right away:
 *   POST data-target-url, body type=<data-target-type>; notifyMessage from the JSON response is shown.
 */
export default class extends Controller {
    connect() {
        if (!this.element.hasAttribute('data-target-url')) {
            return;
        }

        this.onChange = () => this.send();
        this.element.addEventListener('change', this.onChange);
    }

    disconnect() {
        if (this.onChange) {
            this.element.removeEventListener('change', this.onChange);
            this.onChange = null;
        }
    }

    send() {
        const processResponse = response => {
            if (!response || !response.notifyMessage) {
                return;
            }

            for (const type in response.notifyMessage) {
                notifyMessage(type, response.notifyMessage[type]);
            }
        };

        $.ajax({
            type: 'POST',
            url: this.element.getAttribute('data-target-url'),
            data: { type: this.element.getAttribute('data-target-type') },
            timeout: 10000,
            success: response => processResponse(response),
            error: response => processResponse(response.responseJSON)
        });
    }
}
