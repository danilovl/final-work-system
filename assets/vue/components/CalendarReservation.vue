<template>
  <FullCalendar ref="fullCalendar" :options="calendarOptions"/>
</template>

<script lang="ts" setup>
import {onMounted, onUnmounted, ref} from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

interface CalendarReservationProps {
  locale: string;
  endpointEventCalendarEventsAjax: string;
}

const {
  locale,
  endpointEventCalendarEventsAjax
} = defineProps<CalendarReservationProps>();

const fullCalendar = ref(null);

const calendarOptions = {
  plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
  },
  initialView: 'dayGridMonth',
  editable: false,
  selectable: false,
  selectMirror: false,
  dayMaxEvents: true,
  weekends: true,
  select: handleDateSelect,
  eventClick: handleEventClick,
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

function handleDateSelect(selectInfo) {
  const start = dateFormat(selectInfo.start);
  const end = dateFormat(selectInfo.end);
  document.getElementById('event_start').value = start;
  document.getElementById('event_end').value = end;

  $('#create-new-event-modal').modal('show');
}

function handleEventClick(clickInfo) {
  const event = clickInfo.event;
  document.getElementById('modal-title').innerHTML = event.title.replace(/\n/g, '<br>');

  if (event.extendedProps.detail_url) {
    $('#event-detail').show();
    document.getElementById('event-detail').setAttribute('href', '');
    document.getElementById('event-detail').setAttribute('href', event.extendedProps.detail_url);
  } else {
    $('#event-detail').hide();
  }

  if (event.extendedProps.reservation_url) {
    $('#modal-work-form').show();
    $('#event-reservation').show();
    document.getElementById('event-reservation').setAttribute('href', '');
    document.getElementById('event-reservation').setAttribute('href', event.extendedProps.reservation_url);
    document.getElementById('event-reservation').setAttribute('data-event-id', '');
    document.getElementById('event-reservation').setAttribute('data-event-id', event.id);
  } else {
    $('#event-reservation').hide();
    $('#modal-work-form').hide();
  }

  $('#reservation-event-modal').modal('show');
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
                reservation_url: event.reservation_url,
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

const handleReservationClick = function () {
  const href = this.getAttribute('href');
  const form = document.querySelector('form');
  const eventId = this.getAttribute('data-event-id');

  const fetchOptions = {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams(new FormData(form)).toString(),
  };

  fetch(href, fetchOptions)
      .then(response => response.json())
      .then(data => {
        for (let type in data.notifyMessage) {
          notifyMessage(type, data.notifyMessage[type]);
        }

        if (data.valid === true) {
          const calendarApi = fullCalendar.value.getApi();
          const event = calendarApi.getEventById(eventId);
          if (event) {
            event.remove();
          }

          calendarApi.addEvent({
            id: data.event.id,
            title: data.event.title,
            start: data.event.start,
            end: data.event.end,
            color: data.event.color,
            extendedProps: {
              detail_url: data.event.detail_url,
            },
            allDay: false,
          });
        }
      });
};

const eventReservation = document.getElementById('event-reservation');

onMounted(() => {
  eventReservation.addEventListener('click', handleReservationClick);
});

onUnmounted(() => {
  eventReservation.removeEventListener('click', handleReservationClick);
});
</script>
