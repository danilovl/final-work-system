import {Modal} from 'bootstrap';

export function showModal(id: string): void {
    const element = document.getElementById(id);

    if (element !== null) {
        Modal.getOrCreateInstance(element).show();
    }
}

export function hideModal(id: string): void {
    const element = document.getElementById(id);

    if (element !== null) {
        Modal.getOrCreateInstance(element).hide();
    }
}
