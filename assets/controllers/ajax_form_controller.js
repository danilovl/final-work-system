import { Controller } from '@hotwired/stimulus';

/**
 * Submits the parent form of the button via AJAX.
 *
 *   <button data-controller="ajax-form" data-ajax-form-close-value="true" data-ajax-form-file-value="true">
 *
 * Values:
 *   close – close the modal window after a successful save
 *   file  – the form contains files
 */
export default class extends Controller {
    static values = {
        close: Boolean,
        file: Boolean
    };

    connect() {
        if (this.fileValue) {
            createEditContentFileAjax(this.element, this.closeValue);

            return;
        }

        createEditContentAjax(this.element, this.closeValue);
    }
}
