import { createActionGroup, props } from '@ngrx/store';

export const NavigationActions = createActionGroup({
  source: 'Navigation',
  events: {
    Navigate: props<{ target: string | 'back' }>(),
  },
});
