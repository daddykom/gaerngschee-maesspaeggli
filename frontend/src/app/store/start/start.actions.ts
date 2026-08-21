import { createActionGroup, emptyProps, props } from '@ngrx/store';

export const StartActions = createActionGroup({
  source: 'Start',
  events: {
    Submit: props<{ email: string; language: string }>(),
    'Submit Success': props<{ sent: boolean }>(),
    'Submit Failure': emptyProps(),
  },
});
