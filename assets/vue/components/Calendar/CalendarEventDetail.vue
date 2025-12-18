<template>
  <FullCalendar ref="fullCalendar" :options="calendarOptions"/>
</template>

<script lang="ts" setup>
import {ref} from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import {CalendarBaseProps, RawEvent,} from './interface';
import {createFetchEventsFunction} from './function';

const {
  locale,
  endpointEventCalendarEventsAjax,
} = defineProps<CalendarBaseProps>();

const fullCalendar = ref<InstanceType<typeof FullCalendar>>();

const mapEventExtendedProps = (event: RawEvent) => ({
  detail_url: event.detail_url,
  delete_url: event.delete_url,
});

const fetchEvents = createFetchEventsFunction(
    endpointEventCalendarEventsAjax,
    mapEventExtendedProps
);

const calendarOptions: Record<string, any> = {
  plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
  },
  initialView: 'dayGridMonth',
  editable: true,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  events: fetchEvents,
  locale,
  eventColor: '#59ff00',
};
</script>
