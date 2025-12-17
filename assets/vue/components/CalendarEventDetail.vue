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

interface CalendarEventDetailProps {
  locale: string;
  endpointEventCalendarEventsAjax: string;
}

const {
  locale,
  endpointEventCalendarEventsAjax,
} = defineProps<CalendarEventDetailProps>();

const fullCalendar = ref(null);

const calendarOptions = {
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

function dateFormat(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');

  return `${year}-${month}-${day} ${hours}:${minutes}`;
}

function fetchEvents(fetchInfo, successCallback, failureCallback) {
  const start = dateFormat(fetchInfo.start);
  const end = dateFormat(fetchInfo.end);

  const formData = new FormData();
  formData.append('start', start);
  formData.append('end', end);

  fetch(endpointEventCalendarEventsAjax, {method: 'POST', body: formData})
      .then(response => response.json())
      .then(response => {
        if (response.valid) {
          let events = [];
          let respEvents = response.events;

          if (typeof respEvents === 'string') {
            respEvents = JSON.parse(respEvents);
          }

          if (Array.isArray(respEvents)) {
            events = respEvents.map((event) => ({
              id: event.id,
              title: event.title,
              start: event.start,
              end: event.end,
              color: event.color,
              extendedProps: {
                detail_url: event.detail_url,
                delete_url: event.delete_url,
              },
              allDay: false,
            }));
          }
          successCallback(events);
        } else {
          successCallback([]);
        }

        for (let type in response.notifyMessage) {
          notifyMessage(type, response.notifyMessage[type]);
        }
      })
      .catch(error => {
        failureCallback(error);

        if (error.responseJSON && error.responseJSON.notifyMessage) {
          for (let type in error.responseJSON.notifyMessage) {
            notifyMessage(type, error.responseJSON.notifyMessage[type]);
          }
        }
      });
}
</script>
