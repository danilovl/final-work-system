import { Controller } from '@hotwired/stimulus';

/**
 * Work detail page: fixed work info on scroll, deadline countdowns and fixed table headers in tabs.
 *
 *   <div data-controller="work-detail"
 *        data-work-detail-day-label-value="days"
 *        data-work-detail-hour-label-value="hours"
 *        data-work-detail-minute-label-value="minutes"
 *        data-work-detail-second-label-value="seconds">
 *       <div data-work-detail-target="fixedInfo">
 *       <div data-work-detail-target="mainInfo">
 *       <span data-work-detail-target="countdown" data-deadline="2030-01-01">
 */
export default class extends Controller {
    static targets = ['fixedInfo', 'mainInfo', 'countdown'];

    static values = {
        dayLabel: String,
        hourLabel: String,
        minuteLabel: String,
        secondLabel: String
    };

    connect() {
        this.onScroll = this.toggleFixedInfo.bind(this);
        window.addEventListener('scroll', this.onScroll);

        this.intervals = this.countdownTargets.map(element => {
            const deadline = new Date(element.dataset.deadline);
            const interval = setInterval(() => {
                this.showRemaining(deadline, element, interval);
            }, 1000);

            return interval;
        });

        $('#table-task').workTableFix('tab-task');
        $('#table-version').workTableFix('tab-version');
        $('#table-event').workTableFix('tab-event');
    }

    disconnect() {
        window.removeEventListener('scroll', this.onScroll);
        this.intervals.forEach(interval => clearInterval(interval));
    }

    toggleFixedInfo() {
        const mainInfoTop = $(this.mainInfoTarget).offset().top;
        const isScrolledBelow = $(window).scrollTop() > mainInfoTop;

        $(this.fixedInfoTarget).toggleClass('d-none', !isScrolledBelow);
    }

    showRemaining(deadline, element, interval) {
        const distance = deadline - new Date();
        const second = 1000;
        const minute = second * 60;
        const hour = minute * 60;
        const day = hour * 24;

        if (distance < 0) {
            clearInterval(interval);
            element.innerHTML = 'EXPIRED!';

            return;
        }

        const days = Math.floor(distance / day);
        const hours = Math.floor((distance % day) / hour);
        const minutes = Math.floor((distance % hour) / minute);
        const seconds = Math.floor((distance % minute) / second);

        element.innerHTML = `${days} ${this.dayLabelValue} ${hours} ${this.hourLabelValue} ${minutes} ${this.minuteLabelValue} ${seconds} ${this.secondLabelValue} `;
    }
}
