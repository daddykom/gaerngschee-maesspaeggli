import { createActionGroup, emptyProps, props } from '@ngrx/store';

export const AnmeldungActions = createActionGroup({
  source: 'Anmeldung',
  events: {
    Submit: props<{ email: string; language: string }>(),
    'Submit Success': props<{ sent: boolean }>(),
    'Submit Failure': emptyProps(),
  },
});
