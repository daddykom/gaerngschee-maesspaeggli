import { HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { Router } from '@angular/router';
import { EMPTY, catchError, exhaustMap, map, of, tap } from 'rxjs';
import { AuthService } from '../../shared/services/auth.service';
import { AuthActions } from './auth.actions';

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

export const navigateOnLoginSuccessEffect = createEffect(
  (actions$ = inject(Actions), router = inject(Router)) =>
    actions$.pipe(
      ofType(AuthActions.loginSuccess),
      tap(({ requiredPasswordReset }) => {
        void router.navigateByUrl(requiredPasswordReset ? '/password-change' : '/admin/overview');
      }),
    ),
  { functional: true, dispatch: false },
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
  (actions$ = inject(Actions), router = inject(Router)) => actions$.pipe(
    ofType(AuthActions.passwordChangeSuccess),
    tap(() => {
      void router.navigateByUrl('/admin/overview');
    }),
  ),
  { functional: true, dispatch: false },
);

export const logoutEffect = createEffect(
  (actions$ = inject(Actions), authService = inject(AuthService), router = inject(Router)) =>
    actions$.pipe(
      ofType(AuthActions.logout),
      exhaustMap(() =>
        authService.logout().pipe(
          tap(() => {
            void router.navigateByUrl('/login');
          }),
          catchError(() => {
            void router.navigateByUrl('/login');
            return EMPTY;
          }),
        ),
      ),
    ),
  { functional: true, dispatch: false },
);

export const authEffects = {
  loginEffect,
  navigateOnLoginSuccessEffect,
  passwordChangeEffect,
  navigateOnPasswordChangeSuccessEffect,
  logoutEffect,
};
