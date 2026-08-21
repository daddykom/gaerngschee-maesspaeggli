import { createActionGroup, emptyProps, props } from '@ngrx/store';
import { UserGroup } from '../../shared/models/frontend-config.model';

export const AuthActions = createActionGroup({
  source: 'Auth',
  events: {
    Login: props<{ email: string; password: string }>(),
    'Login Success': props<{ token: string; group: UserGroup }>(),
    'Login Failure': props<{ errorCode: string }>(),
    Logout: emptyProps(),
  },
});
