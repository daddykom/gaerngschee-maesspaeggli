import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of } from 'rxjs';
import { AnmeldungService } from '../../shared/services/anmeldung.service';
import { AnmeldungActions } from './anmeldung.actions';

export const submitAnmeldungEffect = createEffect(
  (actions$ = inject(Actions), anmeldungService = inject(AnmeldungService)) =>
    actions$.pipe(
      ofType(AnmeldungActions.submit),
      exhaustMap(({ email, language }) =>
        anmeldungService.requestInformation(email, language).pipe(
          map(({ sent }) => AnmeldungActions.submitSuccess({ sent })),
          catchError(() => of(AnmeldungActions.submitFailure())),
        ),
      ),
    ),
  { functional: true },
);

export const anmeldungEffects = [submitAnmeldungEffect];
