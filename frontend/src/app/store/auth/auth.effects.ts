import { HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of, tap } from 'rxjs';
import { AuthService } from '../../shared/services/auth.service';
import { AuthActions } from './auth.actions';
import { NavigationActions } from '../navigation/navigation.actions';
import { NotificationActions } from '../notification/notification.actions';
import { clearPersistedAuthState, persistAuthState } from '../../shared/services/auth-storage';

export const loginEffect = createEffect(
  (actions$ = inject(Actions), authService = inject(AuthService)) =>
    actions$.pipe(
      ofType(AuthActions.login),
      exhaustMap(({ email, password }) =>
        authService.login(email, password).pipe(
          map(({ token, user, group, requiredPasswordReset }) => AuthActions.loginSuccess({
            token,
            userId: user.id,
            group,
            requiredPasswordReset,
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

export const registrationLoginEffect = createEffect(
  (actions$ = inject(Actions), authService = inject(AuthService)) =>
    actions$.pipe(
      ofType(AuthActions.registrationLogin),
      exhaustMap(({ token }) => authService.registrationLogin(token).pipe(
        map((response) => AuthActions.registrationLoginSuccess({
          token: response.token,
          userId: response.user.id,
          group: response.group,
          fairgateUserExists: response.fairgateUserExists,
          childrenCount: response.childrenCount,
          adultsCount: response.adultsCount,
          salutation: response.salutation,
        })),
        catchError((error: HttpErrorResponse) => of(AuthActions.registrationLoginFailure({
          errorCode: typeof error.error?.error?.code === 'string'
            ? error.error.error.code
            : 'REGISTRATION_LOGIN_FAILED',
        }))),
      )),
    ),
  { functional: true },
);

export const persistLoginEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(AuthActions.loginSuccess),
    tap(({ token, userId, group }) => {
      persistAuthState({
        token,
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
    tap(({ token, userId, group, fairgateUserExists, childrenCount, adultsCount, salutation }) => {
      persistAuthState({
        token,
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
    ofType(AuthActions.logout),
    tap(() => {
      clearPersistedAuthState();
    }),
  ),
  { functional: true, dispatch: false },
);

export const navigateOnRegistrationLoginSuccessEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(AuthActions.registrationLoginSuccess),
    map(() => NavigationActions.navigate({ target: '/order/edit' })),
  ),
  { functional: true },
);

export const navigateOnLoginSuccessEffect = createEffect(
  (actions$ = inject(Actions)) =>
    actions$.pipe(
      ofType(AuthActions.loginSuccess),
      map(({ requiredPasswordReset }) => NavigationActions.navigate({
        target: requiredPasswordReset ? '/password-change' : '/admin/overview',
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

export const navigateOnPasswordChangeSuccessEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(AuthActions.passwordChangeSuccess),
    map(() => NavigationActions.navigate({ target: '/admin/overview' })),
  ),
  { functional: true },
);

export const logoutEffect = createEffect(
  (actions$ = inject(Actions), authService = inject(AuthService)) =>
    actions$.pipe(
      ofType(AuthActions.logout),
      exhaustMap(() =>
        authService.logout().pipe(
          map(() => NavigationActions.navigate({ target: '/login' })),
          catchError(() => of(NavigationActions.navigate({ target: '/login' }))),
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
      AuthActions.registrationLoginFailure,
    ),
    map((action) => NotificationActions.show({
      variant: 'error',
      titleKey: action.type === AuthActions.passwordChangeFailure.type
        ? 'app.passwordChange.heading'
        : action.type === AuthActions.registrationLoginFailure.type
          ? 'app.clientLogin.errorTitle'
          : 'app.auth.loginErrorTitle',
      messageKey: action.type === AuthActions.passwordChangeFailure.type
        ? `app.passwordChange.errors.${action.errorCode}`
        : action.type === AuthActions.registrationLoginFailure.type
          ? `app.clientLogin.errors.${action.errorCode}`
          : `app.auth.errors.${action.errorCode}`,
      ...(action.type === AuthActions.registrationLoginFailure.type
        ? { preserveOnRoutes: ['/client-login'] }
        : {}),
    })),
  ),
  { functional: true },
);

export const authEffects = {
  loginEffect,
  registrationLoginEffect,
  persistLoginEffect,
  persistRegistrationLoginEffect,
  clearPersistedAuthEffect,
  navigateOnRegistrationLoginSuccessEffect,
  navigateOnLoginSuccessEffect,
  passwordChangeEffect,
  navigateOnPasswordChangeSuccessEffect,
  logoutEffect,
  authNotificationEffect,
};
