import { Controller } from '@hotwired/stimulus';

/**
 * select2 for a <select>.
 *
 *   {{ form_widget(form.type, {'attr': {'data-controller': 'select2'}}) }}
 *
 * Values:
 *   width            – e.g. "100%" (select2 default when empty)
 *   dropdown-parent  – CSS selector of the element the dropdown is attached to (needed inside modals)
 *   optgroup         – selectable optgroups (select2.optgroupSelect.js), keeps the dropdown open
 */
export default class extends Controller {
    static values = {
        width: String,
        dropdownParent: String,
        optgroup: Boolean
    };

    connect() {
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
            return;
        }

        const options = {};

        if (this.widthValue) {
            options.width = this.widthValue;
        }

        if (this.dropdownParentValue) {
            options.dropdownParent = $(this.dropdownParentValue);
        }

        if (!this.optgroupValue) {
            this.init(options);

            return;
        }

        $.fn.select2.amd.require(['optgroup-data', 'optgroup-results'], (OptgroupData, OptgroupResults) => {
            this.init({
                ...options,
                dataAdapter: OptgroupData,
                resultsAdapter: OptgroupResults,
                closeOnSelect: false
            });
        });
    }

    disconnect() {
        const $element = $(this.element);

        if ($element.hasClass('select2-hidden-accessible')) {
            $element.select2('destroy');
        }
    }

    init(options) {
        if (!this.element.isConnected) {
            return;
        }

        $(this.element).select2(options);
    }
}
