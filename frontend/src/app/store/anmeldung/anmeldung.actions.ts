import { createActionGroup, props } from '@ngrx/store';

export const AnmeldungActions = createActionGroup({
  source: 'Anmeldung',
  events: {
    Submit: props<{ email: string }>(),
    'Submit Success': props<{ sent: boolean }>(),
    'Submit Failure': props<{ error: string }>(),
  },
});
