import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of } from 'rxjs';
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

export const showStartSuccessNotificationEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(StartActions.submitSuccess),
    map(() => NotificationActions.show({
      variant: 'success',
      titleKey: 'app.anmeldung.emailSentTitle',
      messageKey: 'app.anmeldung.emailSentMessage',
      preserveOnRoutes: ['/start'],
    })),
  ),
  { functional: true },
);

export const startEffects = {
  submitStartEffect,
  showStartSuccessNotificationEffect,
};
