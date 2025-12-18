import {createApp} from 'vue';
import Calendar from './components/Calendar/CalendarManage.vue';

const appCalendarManage = createApp({
    components: {
        Calendar
    }
});

appCalendarManage.mount('#calendar-app');
