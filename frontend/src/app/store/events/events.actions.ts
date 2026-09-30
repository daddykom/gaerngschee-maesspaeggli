import { createActionGroup, props } from '@ngrx/store';

export const EventActions = createActionGroup({
  source: 'Events',
  events: {
    Received: props<{ key: string; value: number }>(),
  },
});
