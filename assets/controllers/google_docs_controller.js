import { Controller } from '@hotwired/stimulus';

/**
 * Shows a file in the Google Docs viewer on click.
 *
 *   <a data-controller="google-docs"
 *      data-action="google-docs#show"
 *      data-google-docs-iframe-param="#iframe-version-content-…"
 *      data-google-docs-url-param="…">
 */
export default class extends Controller {
    show(event) {
        showGoogleDocs(event.params.iframe, event.params.url);
    }
}
