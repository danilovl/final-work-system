import { Controller } from '@hotwired/stimulus';
import { Calendar, time } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/index.css';

/**
 * Vanilla Calendar Pro date picker for an <input>.
 *
 *   {{ form_widget(form.deadline, {'attr': {'data-controller': 'datetimepicker'}}) }}
 *   {{ form_widget(form.start, {'attr': {'data-controller': 'datetimepicker', 'data-datetimepicker-format-value': 'YYYY-MM-DD HH:mm'}}) }}
 *
 * Values:
 *   format – "YYYY-MM-DD" (default) or "YYYY-MM-DD HH:mm" with a 24-hour time picker
 *
 * Language is taken from <html lang="…">. Setting the input value and dispatching "change" updates the calendar.
 */
export default class extends Controller {
    static values = {
        format: { type: String, default: 'YYYY-MM-DD' }
    };

    connect() {
        const withTime = this.formatValue.includes('HH:mm');
        const [date, selectedTime] = this.element.value.split(' ');

        const options = {
            inputMode: true,
            positionToInput: 'auto',
            selectedTheme: 'light',
            locale: document.documentElement.lang || 'en',
            selectedDates: date ? [date] : [],
            onChangeToInput: self => {
                const [selectedDate] = self.context.selectedDates;

                if (selectedDate === undefined) {
                    this.element.value = '';
                } else {
                    this.element.value = withTime ? `${selectedDate} ${self.context.selectedTime}` : selectedDate;
                }

                this.element.dispatchEvent(new Event('change', { bubbles: true }));

                if (!withTime) {
                    self.hide();
                }
            }
        };

        if (withTime) {
            options.extensions = [time];
            options.selectionTimeMode = 24;

            if (selectedTime) {
                options.selectedTime = selectedTime;
            }
        }

        this.calendar = new Calendar(this.element, options);
        this.calendar.init();

        this.onChange = () => {
            const [inputDate] = this.element.value.split(' ');
            const [calendarDate] = this.calendar.context.selectedDates;

            if (inputDate && inputDate !== calendarDate) {
                this.calendar.set({ selectedDates: [inputDate] });
            }
        };
        this.element.addEventListener('change', this.onChange);
    }

    disconnect() {
        this.element.removeEventListener('change', this.onChange);
        this.calendar.destroy();
    }
}
