import { Controller } from '@hotwired/stimulus';

/**
 * jquery.collection for every ".collection" inside the element.
 *
 *   {{ form_start(form, {'attr': {'data-controller': 'collection'}}) }}
 */
export default class extends Controller {
    connect() {
        $(this.element).find('.collection').collection({
            drag_drop: true,
            allow_duplicate: true
        });
    }
}
