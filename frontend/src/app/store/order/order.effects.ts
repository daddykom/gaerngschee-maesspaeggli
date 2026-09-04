import { HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of } from 'rxjs';
import { OrderService } from '../../shared/services/order.service';
import { OrderActions } from './order.actions';

const errorCode = (error: HttpErrorResponse): string =>
  typeof error.error?.error?.code === 'string' ? error.error.error.code : 'REQUEST_FAILED';

export const loadCurrentOrderEffect = createEffect(
  (actions$ = inject(Actions), service = inject(OrderService)) => actions$.pipe(
    ofType(OrderActions.loadCurrent),
    exhaustMap(() => service.getCurrent().pipe(
      map(({ order }) => OrderActions.loadCurrentSuccess({ order })),
      catchError((error: HttpErrorResponse) => of(OrderActions.loadCurrentFailure({ errorCode: errorCode(error) }))),
    )),
  ),
  { functional: true },
);

export const orderEffects = { loadCurrentOrderEffect };
