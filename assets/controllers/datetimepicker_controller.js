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
 * Typing in the input, Escape and Tab close the calendar.
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
            positionToInput: ['bottom', 'left'],
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

        this.onKeydown = event => {
            if (event.key === 'Escape' || event.key === 'Tab') {
                this.calendar.hide();
            }
        };
        this.element.addEventListener('keydown', this.onKeydown);

        this.onInput = () => this.calendar.hide();
        this.element.addEventListener('input', this.onInput);

        this.onBeforeShow = () => this.updatePosition();
        this.element.addEventListener('mousedown', this.onBeforeShow);
        this.element.addEventListener('focus', this.onBeforeShow);

        this.icon = this.element.parentElement.querySelector(':scope > .form-control-feedback');
        this.onIconClick = () => {
            this.updatePosition();
            this.element.focus();
            this.calendar.show();
        };
        this.icon?.addEventListener('click', this.onIconClick);
    }

    updatePosition() {
        const rect = this.element.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        const spaceAbove = rect.top;
        const calendarHeight = this.calendar.context.mainElement?.offsetHeight ?? 0;
        const fitsBelow = calendarHeight > 0 && spaceBelow >= calendarHeight;
        const vertical = fitsBelow || spaceBelow >= spaceAbove ? 'bottom' : 'top';

        this.calendar.positionToInput = [vertical, 'left'];
    }

    disconnect() {
        this.element.removeEventListener('change', this.onChange);
        this.element.removeEventListener('keydown', this.onKeydown);
        this.element.removeEventListener('input', this.onInput);
        this.element.removeEventListener('mousedown', this.onBeforeShow);
        this.element.removeEventListener('focus', this.onBeforeShow);
        this.icon?.removeEventListener('click', this.onIconClick);
        this.calendar.destroy();
    }
}
