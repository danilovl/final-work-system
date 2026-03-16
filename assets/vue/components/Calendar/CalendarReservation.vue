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
  CalendarBaseProps,
  RawEvent,
  ClickInfo,
  ApiResponse
} from './interface';
import {
  createFetchEventsFunction,
  createHandleDateSelectFunction
} from './function';

declare function notifyMessage(type: string, message: string): void;

interface CalendarReservationProps extends CalendarBaseProps {}

const {
  locale,
  endpointEventCalendarEventsAjax
} = defineProps<CalendarReservationProps>();

const fullCalendar = ref<InstanceType<typeof FullCalendar>>();

const handleDateSelect = createHandleDateSelectFunction();

const mapEventExtendedProps = (event: RawEvent) => ({
  detail_url: event.detail_url,
  reservation_url: event.reservation_url,
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

function handleEventClick(clickInfo: ClickInfo): void {
  const event = clickInfo.event;
  const modalTitle = document.getElementById('modal-title');
  const eventDetail = document.getElementById('event-detail');
  const eventReservation = document.getElementById('event-reservation');

  if (modalTitle){
    modalTitle.innerHTML = event.title.replace(/\n/g, '<br>');
  }

  if (event.extendedProps.detail_url) {
    $('#event-detail').show();
    if (eventDetail) {
      eventDetail.setAttribute('href', '');
      eventDetail.setAttribute('href', event.extendedProps.detail_url);
    }
  } else {
    $('#event-detail').hide();
  }

  if (event.extendedProps.reservation_url) {
    $('#modal-work-form').show();
    $('#event-reservation').show();
    if (eventReservation) {
      eventReservation.setAttribute('href', '');
      eventReservation.setAttribute('href', event.extendedProps.reservation_url);
      eventReservation.setAttribute('data-event-id', '');
      eventReservation.setAttribute('data-event-id', event.id);
    }
  } else {
    $('#event-reservation').hide();
    $('#modal-work-form').hide();
  }

  $('#reservation-event-modal').modal('show');
}


const handleReservationClick = function (this: HTMLElement): void {
  const href = this.getAttribute('href');
  const form = document.querySelector('form');
  const eventId = this.getAttribute('data-event-id');

  if (!href || !form || !eventId) {
    return;
  }

  const formData = new FormData(form);

  fetch(href, {method: 'POST', body: formData})
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

        if (response.valid && response.event) {
          const calendarApi = fullCalendar.value?.getApi();
          if (calendarApi) {
            const event = calendarApi.getEventById(eventId);
            if (event) {
              event.remove();
            }

            calendarApi.addEvent({
              id: response.event.id,
              title: response.event.title,
              start: response.event.start,
              end: response.event.end,
              color: response.event.color,
              extendedProps: {
                detail_url: response.event.detail_url,
              },
              allDay: false,
            });
          }
        }
      });
};

const eventReservation = document.getElementById('event-reservation') as HTMLElement;

onMounted(() => {
  eventReservation.addEventListener('click', handleReservationClick);
});

onUnmounted(() => {
  eventReservation.removeEventListener('click', handleReservationClick);
});
</script>
