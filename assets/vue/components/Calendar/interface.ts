export interface CalendarBaseProps {
  locale: string;
  endpointEventCalendarEventsAjax: string;
}

export interface CalendarManageProps extends CalendarBaseProps {
  endpointEventCalendarEditAjax: string;
  endpointEventCalendarCreateAjax: string;
}

export interface CalendarEvent {
  id: string;
  title: string;
  start: string;
  end: string;
  color: string;
  extendedProps: {
    detail_url: string;
    delete_url?: string;
    reservation_url?: string;
  };
  allDay: boolean;
}

export interface RawEvent {
  id: string;
  title: string;
  start: string;
  end: string;
  color: string;
  detail_url: string;
  delete_url?: string;
  reservation_url?: string;
}

export interface FetchInfo {
  start: Date;
  end: Date;
}

export interface SelectInfo {
  start: Date;
  end: Date;
}

export interface EventDropInfo {
  event: {
    id: string;
    start: Date | null;
    end: Date | null;
  };
  revert: () => void;
}

export interface ApiResponse {
  valid: boolean;
  events?: string | RawEvent[];
  event?: RawEvent;
  delete?: boolean;
  notifyMessage: Record<string, string>;
}
