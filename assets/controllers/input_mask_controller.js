import { Controller } from '@hotwired/stimulus';

/**
 * jquery.inputmask for an <input>; the mask itself comes from data-inputmask.
 *
 *   {{ form_widget(form.phone, {
 *       'attr': {
 *           'data-controller': 'input-mask',
 *           'data-inputmask': "'mask' : '+(*{1,3}) *{1,15}'"
 *       }
 *   }) }}
 */
export default class extends Controller {
    connect() {
        if (typeof $ === 'undefined' || typeof $.fn.inputmask === 'undefined') {
            return;
        }

        $(this.element).inputmask();
    }

    disconnect() {
        if (typeof $ !== 'undefined' && typeof $.fn.inputmask !== 'undefined') {
            $(this.element).inputmask('remove');
        }
    }
}
