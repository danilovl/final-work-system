import { Controller } from '@hotwired/stimulus';
import { createImageByWebCamera } from '../js/main.js';

/**
 * Profile image from the web camera.
 *
 *   <div id="create-web-camera-image" data-controller="web-camera" data-url="…">
 */
export default class extends Controller {
    connect() {
        createImageByWebCamera(this.element.id);
    }
}
