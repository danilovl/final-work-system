import {createApp} from 'vue';
import Calendar from './components/CalendarEventDetail.vue';

const app = createApp({
    components: {
        Calendar
    }
});

app.mount('#calendar-app');
