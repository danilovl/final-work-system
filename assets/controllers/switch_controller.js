import { Controller } from '@hotwired/stimulus';

/**
 * Switchery toggle for a checkbox.
 *
 *   <input type="checkbox" data-controller="switch">
 *
 * With data-target-url the new state is sent right away:
 *   POST data-target-url, body type=<data-target-type>; notifyMessage from the JSON response is shown.
 */
export default class extends Controller {
    connect() {
        if (typeof Switchery === 'undefined' || this.switchery) {
            return;
        }

        this.switchery = new Switchery(this.element, {
            color: '#26B99A',
            secondaryColor: '#ff0000'
        });

        if (this.element.hasAttribute('data-target-url')) {
            this.onChange = () => this.send();
            this.element.addEventListener('change', this.onChange);
        }
    }

    disconnect() {
        if (!this.switchery) {
            return;
        }

        if (this.onChange) {
            this.element.removeEventListener('change', this.onChange);
            this.onChange = null;
        }

        this.switchery.destroy();
        if (this.switchery.switcher) {
            this.switchery.switcher.remove();
        }
        this.element.style.display = '';
        this.switchery = null;
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
