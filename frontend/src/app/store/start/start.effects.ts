import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of, tap } from 'rxjs';
import { AnmeldungService } from '../../shared/services/anmeldung.service';
import { StartActions } from './start.actions';
import { NotificationActions } from '../notification/notification.actions';

export const submitStartEffect = createEffect(
  (actions$ = inject(Actions), anmeldungService = inject(AnmeldungService)) =>
    actions$.pipe(
      ofType(StartActions.submit),
      exhaustMap(({ email, language }) =>
        anmeldungService.requestInformation(email, language).pipe(
          map(({ sent }) => StartActions.submitSuccess({ sent })),
          catchError(() => of(StartActions.submitFailure())),
        ),
      ),
    ),
  { functional: true },
);

export const navigateToStartSuccessEffect = createEffect(
  (actions$ = inject(Actions), router = inject(Router)) => actions$.pipe(
    ofType(StartActions.submitSuccess),
    tap(() => void router.navigate(['/start/success'])),
  ),
  { dispatch: false, functional: true },
);

export const showStartFailureNotificationEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(StartActions.submitFailure),
    map(() => NotificationActions.show({
      variant: 'error',
      titleKey: 'app.anmeldung.errorTitle',
      messageKey: 'app.anmeldung.sendError',
      preserveOnRoutes: ['/start'],
    })),
  ),
  { functional: true },
);

export const startEffects = {
  submitStartEffect,
  navigateToStartSuccessEffect,
  showStartFailureNotificationEffect,
};
