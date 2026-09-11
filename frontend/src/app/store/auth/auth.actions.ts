import { createActionGroup, emptyProps, props } from '@ngrx/store';
import { UserGroup } from '../../shared/models/frontend-config.model';

export const AuthActions = createActionGroup({
  source: 'Auth',
  events: {
    Login: props<{ email: string; password: string }>(),
    'Login Success': props<{ token: string; userId: string; group: UserGroup; requiredPasswordReset: boolean; email?: string }>(),
    'Login Failure': props<{ errorCode: string }>(),
    'Token Refreshed': props<{ token: string }>(),
    'Registration Login': props<{ token: string }>(),
    'Registration Login Success': props<{
      token: string;
      userId: string;
      group: UserGroup;
      fairgateUserExists: boolean;
      childrenCount: number;
      adultsCount: number;
      salutation: string;
    }>(),
    'Registration Login Failure': props<{ errorCode: string }>(),
    'Password Change': props<{ password: string }>(),
    'Password Change Success': emptyProps(),
    'Password Change Failure': props<{ errorCode: string }>(),
    'Password Reset Request': props<{ email: string }>(),
    'Password Reset Request Success': emptyProps(),
    'Password Reset Request Failure': props<{ errorCode: string }>(),
    'Password Reset': props<{ token: string; password: string }>(),
    'Password Reset Success': emptyProps(),
    'Password Reset Failure': props<{ errorCode: string }>(),
    'Logout Requested': props<{ redirectTo: '/login' | '/start' }>(),
  },
});
