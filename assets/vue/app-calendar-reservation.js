import {createApp} from 'vue';
import Calendar from './components/CalendarReservation.vue';

const app = createApp({
    components: {
        Calendar
    }
});

app.mount('#calendar-app');
