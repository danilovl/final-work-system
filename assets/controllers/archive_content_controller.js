import { Controller } from '@hotwired/stimulus';
import { showArchiveContent } from '../js/main.js';

/**
 * Lists the files of a zip archive in the element.
 *
 *   <div id="version-content-…"
 *        data-controller="archive-content"
 *        data-archive-content-url-value="…"
 *        data-archive-content-info-id-value="version-content-info-…"></div>
 */
export default class extends Controller {
    static values = {
        url: String,
        infoId: String
    };

    connect() {
        showArchiveContent(this.urlValue, `#${this.element.id}`, this.infoIdValue);
    }
}
