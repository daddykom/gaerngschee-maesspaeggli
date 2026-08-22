import { Location } from '@angular/common';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { Router } from '@angular/router';
import { tap } from 'rxjs';
import { NavigationActions } from './navigation.actions';

export const navigationEffect = createEffect(
  (actions$ = inject(Actions), router = inject(Router), location = inject(Location)) => actions$.pipe(
    ofType(NavigationActions.navigate),
    tap(({ target }) => {
      if (target === 'back') {
        location.back();
        return;
      }

      void router.navigateByUrl(target);
    }),
  ),
  { functional: true, dispatch: false },
);

export const navigationEffects = { navigationEffect };
