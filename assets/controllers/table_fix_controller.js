import { Controller } from '@hotwired/stimulus';

/**
 * Fixed table header for every table inside the element.
 *
 *   <div data-controller="table-fix">…<table>…</table></div>
 *
 * The controller must not be placed on the table itself: tableFix() wraps the table,
 * Stimulus would see it as removed and added again and reconnect the controller endlessly.
 */
export default class extends Controller {
    connect() {
        $(this.element)
            .find('table')
            .not('.table-header-fixed')
            .filter((index, table) => !$(table).parent().hasClass('container-table-fixed'))
            .tableFix();
    }
}
