import { HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of, tap } from 'rxjs';
import { AuthService } from '../../shared/services/auth.service';
import { AuthActions } from './auth.actions';
import { NavigationActions } from '../navigation/navigation.actions';
import { NotificationActions } from '../notification/notification.actions';
import { clearPersistedAuthState, persistAuthState } from '../../shared/services/auth-storage';
import { Store } from '@ngrx/store';
import { selectAuthGroup } from './auth.feature';

export const loginEffect = createEffect(
  (actions$ = inject(Actions), authService = inject(AuthService)) =>
    actions$.pipe(
      ofType(AuthActions.login),
      exhaustMap(({ email, password }) =>
        authService.login(email, password).pipe(
           map(({ user, group, requiredPasswordReset }) => AuthActions.loginSuccess({
             userId: user.id,
             group,
             requiredPasswordReset,
             email: user.email,
          })),
          catchError((error: HttpErrorResponse) =>
            of(AuthActions.loginFailure({
              errorCode: typeof error.error?.error?.code === 'string'
                ? error.error.error.code
                : 'LOGIN_FAILED',
            })),
          ),
        ),
      ),
    ),
  { functional: true },
);

export const persistLoginEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(AuthActions.loginSuccess),
    tap(({ userId, group }) => {
      persistAuthState({
        userId,
        group,
        fairgateUserExists: null,
        childrenCount: null,
        adultsCount: null,
        salutation: null,
      });
    }),
  ),
  { functional: true, dispatch: false },
);

export const persistRegistrationLoginEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(AuthActions.registrationLoginSuccess),
    tap(({ userId, group, fairgateUserExists, childrenCount, adultsCount, salutation }) => {
      persistAuthState({
        userId,
        group,
        fairgateUserExists,
        childrenCount,
        adultsCount,
        salutation,
      });
    }),
  ),
  { functional: true, dispatch: false },
);

export const clearPersistedAuthEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(AuthActions.logoutRequested),
    tap(() => {
      clearPersistedAuthState();
    }),
  ),
  { functional: true, dispatch: false },
);

export const navigateOnLoginSuccessEffect = createEffect(
  (actions$ = inject(Actions)) =>
    actions$.pipe(
      ofType(AuthActions.loginSuccess),
       map(({ requiredPasswordReset, group }) => NavigationActions.navigate({
         target: requiredPasswordReset ? '/password-change' : group === 'user' ? '/delivery' : '/admin/overview',
      })),
    ),
  { functional: true },
);

export const passwordChangeEffect = createEffect(
  (actions$ = inject(Actions), authService = inject(AuthService)) => actions$.pipe(
    ofType(AuthActions.passwordChange),
    exhaustMap(({ password }) => authService.changePassword(password).pipe(
      map(() => AuthActions.passwordChangeSuccess()),
      catchError((error: HttpErrorResponse) => of(AuthActions.passwordChangeFailure({
        errorCode: typeof error.error?.error?.code === 'string'
          ? error.error.error.code
          : 'PASSWORD_CHANGE_FAILED',
      }))),
    )),
  ),
  { functional: true },
);

export const passwordResetRequestEffect = createEffect(
  (actions$ = inject(Actions), authService = inject(AuthService)) => actions$.pipe(
    ofType(AuthActions.passwordResetRequest),
    exhaustMap(({ email }) => authService.requestPasswordReset(email).pipe(
      map(() => AuthActions.passwordResetRequestSuccess()),
      catchError((error: HttpErrorResponse) => of(AuthActions.passwordResetRequestFailure({
        errorCode: typeof error.error?.error?.code === 'string' ? error.error.error.code : 'PASSWORD_RESET_REQUEST_FAILED',
      }))),
    )),
  ),
  { functional: true },
);

export const passwordResetEffect = createEffect(
  (actions$ = inject(Actions), authService = inject(AuthService)) => actions$.pipe(
    ofType(AuthActions.passwordReset),
    exhaustMap(({ token, password }) => authService.resetPassword(token, password).pipe(
      map(() => AuthActions.passwordResetSuccess()),
      catchError((error: HttpErrorResponse) => of(AuthActions.passwordResetFailure({
        errorCode: typeof error.error?.error?.code === 'string' ? error.error.error.code : 'PASSWORD_RESET_FAILED',
      }))),
    )),
  ),
  { functional: true },
);

export const navigateOnPasswordResetSuccessEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(AuthActions.passwordResetSuccess),
    map(() => NavigationActions.navigate({ target: '/login' })),
  ),
  { functional: true },
);

export const navigateOnPasswordChangeSuccessEffect = createEffect(
  (actions$ = inject(Actions), store = inject(Store, { optional: true })) => actions$.pipe(
    ofType(AuthActions.passwordChangeSuccess),
    map(() => NavigationActions.navigate({ target: store?.selectSignal(selectAuthGroup)() === 'user' ? '/delivery' : '/admin/overview' })),
  ),
  { functional: true },
);

export const logoutEffect = createEffect(
  (actions$ = inject(Actions), authService = inject(AuthService)) =>
    actions$.pipe(
      ofType(AuthActions.logoutRequested),
      exhaustMap(({ redirectTo }) =>
        authService.logout().pipe(
          map(() => NavigationActions.navigate({ target: redirectTo })),
          catchError(() => of(NavigationActions.navigate({ target: redirectTo }))),
        ),
      ),
    ),
  { functional: true },
);

export const authNotificationEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(
      AuthActions.loginFailure,
      AuthActions.passwordChangeFailure,
      AuthActions.passwordResetRequestFailure,
      AuthActions.passwordResetFailure,
      AuthActions.registrationLoginFailure,
    ),
    map((action) => NotificationActions.show({
      variant: 'error',
       titleKey: action.type === AuthActions.passwordChangeFailure.type
         ? 'app.passwordChange.heading'
         : action.type === AuthActions.registrationLoginFailure.type
           ? 'app.anmeldung.errorTitle'
           : action.type === AuthActions.passwordResetRequestFailure.type
             ? 'app.passwordResetRequest.title'
             : action.type === AuthActions.passwordResetFailure.type
               ? 'app.passwordReset.title'
               : 'app.auth.loginErrorTitle',
       messageKey: action.type === AuthActions.passwordChangeFailure.type
         ? `app.passwordChange.errors.${action.errorCode}`
         : action.type === AuthActions.passwordResetRequestFailure.type
           ? 'app.passwordResetRequest.error'
           : action.type === AuthActions.passwordResetFailure.type
             ? 'app.passwordReset.errors.invalid'
             : action.type === AuthActions.registrationLoginFailure.type
               ? 'app.anmeldung.registrationTokenError'
               : `app.auth.errors.${action.errorCode}`,
        ...(action.type === AuthActions.registrationLoginFailure.type
          ? { preserveOnRoutes: ['/start'] }
        : {}),
    })),
  ),
  { functional: true },
);

export const authEffects = {
  loginEffect,
  persistLoginEffect,
  persistRegistrationLoginEffect,
  clearPersistedAuthEffect,
  navigateOnLoginSuccessEffect,
  passwordChangeEffect,
  passwordResetRequestEffect,
  passwordResetEffect,
  navigateOnPasswordResetSuccessEffect,
  navigateOnPasswordChangeSuccessEffect,
  logoutEffect,
  authNotificationEffect,
};
