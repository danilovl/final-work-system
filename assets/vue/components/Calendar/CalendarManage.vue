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
import {
  CalendarManageProps,
  RawEvent,
  ClickInfo,
  EventDropInfo,
  ApiResponse
} from './interface';
import {
  dateFormat,
  createFetchEventsFunction,
  createHandleDateSelectFunction
} from './function';

const {
  locale,
  endpointEventCalendarEventsAjax,
  endpointEventCalendarEditAjax,
  endpointEventCalendarCreateAjax
} = defineProps<CalendarManageProps>();

const fullCalendar = ref<InstanceType<typeof FullCalendar>>();
const handleDateSelect = createHandleDateSelectFunction();
const fetchEvents = createFetchEventsFunction(
    endpointEventCalendarEventsAjax,
    (event: RawEvent) => ({
      detail_url: event.detail_url,
      delete_url: event.delete_url,
    })
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
  select: handleDateSelect,
  eventClick: handleEventClick,
  events: fetchEvents,
  eventDrop: handleEventDrop,
  eventResize: handleEventResize,
  locale,
  eventColor: '#59ff00',
};

function handleEventClick(clickInfo: ClickInfo): void {
  const event = clickInfo.event;
  const modalTitle = document.getElementById('modal-title');
  const eventDetail = document.getElementById('event-detail');
  const eventDelete = document.getElementById('event-delete');

  if (modalTitle) modalTitle.innerHTML = event.title.replace(/\n/g, '<br>');
  if (eventDetail) eventDetail.setAttribute('href', event.extendedProps.detail_url);
  if (eventDelete) {
    eventDelete.setAttribute('href', event.extendedProps.delete_url);
    eventDelete.setAttribute('data-event-id', event.id);
  }

  $('#event-detail-modal').modal('show');
}

function handleEventDrop(info: EventDropInfo): void {
  const event = info.event;
  const start = dateFormat(event.start);
  const end = event.end ? dateFormat(event.end) : start;
  const url = endpointEventCalendarEditAjax.replace('Ynvr2lgdAJ', event.id);

  const formData = new FormData();
  formData.append('start', start);
  formData.append('end', end);

  fetch(url, {method: 'POST', body: formData})
      .then(async (response) => {
        try {
          return await response.json();
        } catch (error) {
          throw new Error('Error parsing JSON response: ' + error.message);
        }
      })
      .then((response: ApiResponse) => {
        for (const type in response.notifyMessage) {
          notifyMessage(type, response.notifyMessage[type]);
        }
      })
      .catch((error) => {
        failureCallback(error);
      });
}

function handleEventResize(info: EventDropInfo): void {
  handleEventDrop(info);
}

function createEvent(): void {
  const form = document.querySelector('form');
  const button = document.getElementById('event_create') as HTMLButtonElement;
  const loading = document.getElementById('event_create_load');

  if (!form || !button || !loading) return;

  button.disabled = true;
  loading.classList.remove('hide');

  const formData = new FormData(form);

  fetch(endpointEventCalendarCreateAjax, {method: 'POST', body: formData})
      .then(async (response) => {
        try {
          return await response.json();
        } catch (error) {
          throw new Error('Error parsing JSON response: ' + error.message);
        }
      })
      .then((response: ApiResponse) => {
        $('#create-new-event-modal').modal('hide');

        const calendarApi = fullCalendar.value?.getApi();
        if (calendarApi) {
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

        for (const type in response.notifyMessage) {
          notifyMessage(type, response.notifyMessage[type]);
        }
      })
      .catch((error) => {
        failureCallback(error);
      })
      .finally(() => {
        loading.classList.add('hide');
        button.disabled = false;
      });
}

function deleteEvent(): void {
  const deleteElement = document.getElementById('event-delete');

  if (!deleteElement) return;

  const href = deleteElement.getAttribute('href');
  const eventId = deleteElement.getAttribute('data-event-id');

  if (!href || !eventId) return;

  fetch(href, {method: 'POST'})
      .then(async (response) => {
        try {
          return await response.json();
        } catch (error) {
          throw new Error('Error parsing JSON response: ' + error.message);
        }
      })
      .then((response: ApiResponse) => {
        const responseData = response.data;

        const calendarApi = fullCalendar.value?.getApi();
        if (calendarApi) {
          const event = calendarApi.getEventById(eventId);
          if (event) {
            event.remove();
          }
        }

        for (const type in responseData.notifyMessage) {
          notifyMessage(type, responseData.notifyMessage[type]);
        }
      });
}

const eventCreateElement = document.getElementById('event_create') as HTMLElement;
const eventDeleteElement = document.getElementById('event-delete') as HTMLElement;

onMounted(() => {
  eventCreateElement.addEventListener('click', createEvent);
  eventDeleteElement.addEventListener('click', deleteEvent);
});

onUnmounted(() => {
  eventCreateElement.removeEventListener('click', createEvent);
  eventDeleteElement.removeEventListener('click', deleteEvent);
});
</script>
