import { createActionGroup, emptyProps, props } from '@ngrx/store';

export const SessionActions = createActionGroup({
  source: 'Session',
  events: {
    'Polling Started': emptyProps(),
    'Polling Stopped': emptyProps(),
    'Status Requested': emptyProps(),
    'Status Loaded': props<{ expiresAt: string; secondsRemaining: number }>(),
    'Session Expired': emptyProps(),
    'Refresh Requested': emptyProps(),
    'Refresh Succeeded': props<{ expiresAt: string; secondsRemaining: number }>(),
    'Refresh Failed': emptyProps(),
    'Countdown Started': props<{ expiresAt: string }>(),
    'Countdown Tick': props<{ secondsRemaining: number }>(),
    'Countdown Stopped': emptyProps(),
  },
});
