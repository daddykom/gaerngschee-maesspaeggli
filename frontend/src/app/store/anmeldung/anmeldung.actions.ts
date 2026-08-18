import { createActionGroup, emptyProps, props } from '@ngrx/store';

export const AnmeldungActions = createActionGroup({
  source: 'Anmeldung',
  events: {
    Submit: props<{ email: string }>(),
    'Submit Success': props<{ sent: boolean }>(),
    'Submit Failure': emptyProps(),
  },
});
