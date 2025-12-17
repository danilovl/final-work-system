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

interface CalendarManageProps {
  locale: string;
  endpointEventCalendarEventsAjax: string;
  endpointEventCalendarEditAjax: string;
  endpointEventCalendarCreateAjax: string;
}

const {
  locale,
  endpointEventCalendarEventsAjax,
  endpointEventCalendarEditAjax,
  endpointEventCalendarCreateAjax
} = defineProps<CalendarManageProps>();

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
  select: handleDateSelect,
  eventClick: handleEventClick,
  events: fetchEvents,
  eventDrop: handleEventDrop,
  eventResize: handleEventResize,
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
  document.getElementById('event-detail').setAttribute('href', event.extendedProps.detail_url);
  document.getElementById('event-delete').setAttribute('href', event.extendedProps.delete_url);
  document.getElementById('event-delete').setAttribute('data-event-id', event.id);

  $('#event-detail-modal').modal('show');
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

function handleEventDrop(info) {
  const event = info.event;
  const start = dateFormat(event.start)
  const end = event.end ? dateFormat(event.end) : start;
  const url = endpointEventCalendarEditAjax.replace('Ynvr2lgdAJ', event.id);

  const formData = new FormData();
  formData.append('start', start);
  formData.append('end', end);

  fetch(url, {method: 'POST', body: formData})
      .then(response => response.json())
      .then(response => {
        if (!response.valid) {
          info.revert();
        }

        for (let type in response.notifyMessage) {
          notifyMessage(type, response.notifyMessage[type]);
        }
      })
      .catch(error => {
        info.revert();

        if (error.responseJSON && error.responseJSON.notifyMessage) {
          for (let type in error.responseJSON.notifyMessage) {
            notifyMessage(type, error.responseJSON.notifyMessage[type]);
          }
        }
      });
}

function handleEventResize(info) {
  handleEventDrop(info);
}

function createEvent() {
  const form = document.querySelector('form');
  const button = document.getElementById('event_create');
  const loading = document.getElementById('event_create_load');
  button.disabled = true;
  loading.classList.remove('hide');

  const formData = new FormData(form);

  fetch(endpointEventCalendarCreateAjax, {method: 'POST', body: formData})
      .then(response => response.json())
      .then(response => {
        if (response.valid) {
          $('#create-new-event-modal').modal('hide');

          const calendarApi = fullCalendar.value.getApi();
          calendarApi.addEvent({
            id: response.event.id,
            title: response.event.title,
            start: response.event.start,
            end: response.event.end,
            color: response.event.color,
            extendedProps: {
              detail_url: response.event.detail_url,
              delete_url: response.event.delete_url,
            },
            allDay: false,
          });
        }

        for (let type in response.notifyMessage) {
          notifyMessage(type, response.notifyMessage[type]);
        }
      })
      .catch(error => {
        if (error.responseJSON && error.responseJSON.notifyMessage) {
          for (let type in error.responseJSON.notifyMessage) {
            notifyMessage(type, error.responseJSON.notifyMessage[type]);
          }
        }
      })
      .finally(() => {
        loading.classList.add('hide');
        button.disabled = false;
      });
}

function deleteEvent() {
  const deleteElement = document.getElementById('event-delete');
  const href = deleteElement.getAttribute('href');
  const eventId = deleteElement.getAttribute('data-event-id');

  fetch(href, {method: 'POST'})
      .then(response => response.json())
      .then(response => {
        if (response.delete) {
          const calendarApi = fullCalendar.value.getApi();
          const event = calendarApi.getEventById(eventId);
          if (event) {
            event.remove();
          }
        }

        for (let type in response.notifyMessage) {
          notifyMessage(type, response.notifyMessage[type]);
        }
      })
      .catch(() => {
      });
}

const eventCreateElement = document.getElementById('event_create');
const eventDeleteElement = document.getElementById('event-delete');

onMounted(() => {
  eventCreateElement.addEventListener('click', createEvent);
  eventDeleteElement.addEventListener('click', deleteEvent);
});

onUnmounted(() => {
  eventCreateElement.removeEventListener('click', createEvent);
  eventDeleteElement.removeEventListener('click', deleteEvent);
});
</script>
