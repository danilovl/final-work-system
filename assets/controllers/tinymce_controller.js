import { Controller } from '@hotwired/stimulus';

/**
 * TinyMCE editor for every <textarea> inside the element (or for the element itself).
 *
 *   {{ form_start(form, {'attr': {'data-controller': 'tinymce'}}) }}
 *
 * Language is taken from <html lang="…">.
 */
export default class extends Controller {
    static values = {
        selector: { type: String, default: 'textarea' },
        height: { type: Number, default: 300 }
    };

    connect() {
        if (typeof tinymce === 'undefined') {
            return;
        }

        const elements = this.element.matches(this.selectorValue)
            ? [this.element]
            : Array.from(this.element.querySelectorAll(this.selectorValue));

        if (elements.length === 0) {
            return;
        }

        tinymce.baseURL = window.location.origin + '/build/tinymce';

        this.editors = [];
        elements.forEach(element => {
            tinymce.init({
                target: element,
                height: this.heightValue,
                theme: 'silver',
                language: document.documentElement.lang || 'en',
                image_advtab: true,
                content_css: [
                    '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i'
                ],
                setup: editor => {
                    this.editors.push(editor);
                    editor.on('change', () => editor.save());
                }
            });
        });
    }

    disconnect() {
        (this.editors || []).forEach(editor => editor.remove());
        this.editors = [];
    }
}
