import { Controller } from '@hotwired/stimulus';

/**
 * Picks a date from the list of suggested dates.
 *
 *   {{ form_start(form, {'attr': {'data-controller': 'calendar-helper'}}) }}
 *   <a data-action="calendar-helper#show" data-calendar-helper-panel-param="#deadline-calendar-helper-date">
 *   <div id="deadline-calendar-helper-date" data-calendar-helper-target="panel">
 *   <button data-action="calendar-helper#pick"
 *           data-calendar-helper-input-param="#work_deadline"
 *           data-calendar-helper-date-param="2030-01-01">
 *
 * Panels are shown without the Bootstrap modal (the form is already in a modal) and hidden after a pick.
 */
export default class extends Controller {
    static targets = ['panel'];

    show(event) {
        $(event.params.panel).show().addClass('in');
    }

    pick(event) {
        const input = document.querySelector(event.params.input);
        input.value = String(event.params.date);
        input.dispatchEvent(new Event('change', { bubbles: true }));

        this.panelTargets.forEach(panel => {
            $(panel).hide().removeClass('in');
        });
    }
}
