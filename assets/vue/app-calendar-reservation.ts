import {createApp} from 'vue';
import Calendar from './components/Calendar/CalendarReservation.vue';

const app = createApp({
    components: {
        Calendar
    }
});

app.mount('#calendar-app');
