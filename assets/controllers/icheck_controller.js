import { Controller } from '@hotwired/stimulus';

/**
 * iCheck flat green skin for every checkbox and radio inside the element.
 *
 *   <td data-controller="icheck"><input type="checkbox"></td>
 *
 * The controller must not be placed on the input itself: iCheck wraps the input,
 * Stimulus would see it as removed and added again and reconnect the controller endlessly.
 */
export default class extends Controller {
    connect() {
        $(this.element)
            .find('input[type="checkbox"], input[type="radio"]')
            .filter((index, input) => !$(input).parent().is('.icheckbox_flat-green, .iradio_flat-green'))
            .iCheck({
                checkboxClass: 'icheckbox_flat-green',
                radioClass: 'iradio_flat-green'
            });
    }
}
