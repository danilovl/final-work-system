import { Controller } from '@hotwired/stimulus';

/**
 * eonasdan bootstrap-datetimepicker for an <input>.
 *
 *   {{ form_widget(form.deadline, {'attr': {'data-controller': 'datetimepicker', 'data-datetimepicker-format-value': 'YYYY-MM-DD'}}) }}
 *
 * Language is taken from <html lang="…">.
 */
export default class extends Controller {
    static values = {
        format: { type: String, default: 'YYYY-MM-DD' }
    };

    connect() {
        if (typeof $ === 'undefined' || typeof $.fn.datetimepicker === 'undefined') {
            return;
        }

        $(this.element).datetimepicker({
            format: this.formatValue,
            locale: document.documentElement.lang || 'en'
        });
    }

    disconnect() {
        const picker = $(this.element).data('DateTimePicker');

        if (picker) {
            picker.destroy();
        }
    }
}
