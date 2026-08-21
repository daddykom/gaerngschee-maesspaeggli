import { HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of } from 'rxjs';
import { AuthService } from '../../shared/services/auth.service';
import { AuthActions } from './auth.actions';

export const loginEffect = createEffect(
  (actions$ = inject(Actions), authService = inject(AuthService)) =>
    actions$.pipe(
      ofType(AuthActions.login),
      exhaustMap(({ email, password }) =>
        authService.login(email, password).pipe(
          map(({ token, group }) => AuthActions.loginSuccess({ token, group })),
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

export const authEffects = [loginEffect];
