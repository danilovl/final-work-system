export function dateFormat(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}`;
}

export function createFetchEventsFunction(
    endpointEventCalendarEventsAjax: string,
    mapEventExtendedProps: (event: RawEvent) => any
) {
    return function fetchEvents(
        fetchInfo: FetchInfo,
        successCallback: (events: CalendarEvent[]) => void,
        failureCallback: (error: any) => void
    ): void {
        const start = dateFormat(fetchInfo.start);
        const end = dateFormat(fetchInfo.end);

        const formData = new FormData();
        formData.append('start', start);
        formData.append('end', end);

        fetch(endpointEventCalendarEventsAjax, {method: 'POST', body: formData})
            .then(async (response) => {
                try {
                    return await response.json();
                } catch (error) {
                    throw new Error('Error parsing JSON response: ' + error.message);
                }
            })
            .then((response: ApiResponse) => {
                let events: CalendarEvent[];
                let respEvents: RawEvent[] = response.events as RawEvent[] || [];

                events = respEvents.map((event: RawEvent) => ({
                    id: event.id,
                    title: event.title,
                    start: event.start,
                    end: event.end,
                    color: event.color,
                    extendedProps: mapEventExtendedProps(event),
                    allDay: false,
                }));

                successCallback(events);

                for (const type in response.notifyMessage) {
                    notifyMessage(type, response.notifyMessage[type]);
                }
            })
            .catch((error) => {
                failureCallback(error);
            });
    };
}

export function createHandleDateSelectFunction() {
    return function handleDateSelect(selectInfo: SelectInfo): void {
        const start = dateFormat(selectInfo.start);
        const end = dateFormat(selectInfo.end);
        const startElement = document.getElementById('event_start') as HTMLInputElement;
        const endElement = document.getElementById('event_end') as HTMLInputElement;

        if (startElement) startElement.value = start;
        if (endElement) endElement.value = end;

        $('#create-new-event-modal').modal('show');
    };
}

declare function notifyMessage(type: string, message: string): void;
