import { Controller } from '@hotwired/stimulus';

let isRobotoFontBlocked = false;

/**
 * Google Map with a marker.
 *
 * Read-only:
 *   <div data-controller="google-map"
 *        data-google-map-lat-value="50.07" data-google-map-lng-value="14.40">
 *       <div data-google-map-target="map" style="height: 400px"></div>
 *   </div>
 *
 * Editable (click on the map moves the marker and fills the inputs):
 *   add data-google-map-editable-value="true" and the targets
 *   "street", "latitude", "longitude" on the form inputs.
 */
export default class extends Controller {
    static targets = ['map', 'street', 'latitude', 'longitude'];

    static values = {
        lat: { type: Number, default: 50.0712572 },
        lng: { type: Number, default: 14.4092035 },
        zoom: { type: Number, default: 11 },
        editable: { type: Boolean, default: false }
    };

    connect() {
        if (typeof google === 'undefined' || !google.maps) {
            return;
        }

        blockRobotoFont();

        const position = new google.maps.LatLng(this.latValue, this.lngValue);
        this.map = new google.maps.Map(this.mapTarget, {
            zoom: this.zoomValue,
            center: position
        });
        this.marker = new google.maps.Marker({ position, map: this.map });

        if (this.editableValue) {
            this.geocoder = new google.maps.Geocoder();
            this.clickListener = this.map.addListener('click', event => this.select(event.latLng));
        }
    }

    disconnect() {
        if (this.clickListener) {
            this.clickListener.remove();
        }

        this.map = null;
        this.marker = null;
        this.geocoder = null;
        this.clickListener = null;
    }

    select(latLng) {
        this.marker.setPosition(latLng);

        if (this.hasLatitudeTarget) {
            this.latitudeTarget.value = latLng.lat();
        }

        if (this.hasLongitudeTarget) {
            this.longitudeTarget.value = latLng.lng();
        }

        if (!this.hasStreetTarget) {
            return;
        }

        this.geocoder.geocode({ location: latLng }, (results, status) => {
            if (status === google.maps.GeocoderStatus.OK && results[0]) {
                this.streetTarget.value = results[0].formatted_address;
            }
        });
    }
}

/**
 * Google Maps injects the Roboto font stylesheet into <head>; the theme has its own fonts.
 */
function blockRobotoFont() {
    if (isRobotoFontBlocked) {
        return;
    }

    isRobotoFontBlocked = true;

    const head = document.head;
    const insertBefore = head.insertBefore;

    head.insertBefore = function (newElement, referenceElement) {
        if (newElement.href && newElement.href.indexOf('//fonts.googleapis.com/css?family=Roboto') > -1) {
            return newElement;
        }

        return insertBefore.call(head, newElement, referenceElement);
    };
}
