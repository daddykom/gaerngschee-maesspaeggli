import { HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of } from 'rxjs';
import { DeliveryService } from '../../shared/services/delivery.service';
import { DeliveryActions } from './delivery.actions';

const errorCode = (error: HttpErrorResponse): string =>
  typeof error.error?.error?.code === 'string' ? error.error.error.code : 'REQUEST_FAILED';

export const deliveryLoadEffect = createEffect(
  (actions$ = inject(Actions), service = inject(DeliveryService)) => actions$.pipe(
    ofType(DeliveryActions.loadRequested),
    exhaustMap(({ email, token }) => service.getOrder({ email, token }).pipe(
      map((response) => DeliveryActions.loadSuccess(response)),
      catchError((error: HttpErrorResponse) => of(DeliveryActions.loadFailure({ errorCode: errorCode(error) }))),
    )),
  ),
  { functional: true },
);

export const deliveryStatusChangeEffect = createEffect(
  (actions$ = inject(Actions), service = inject(DeliveryService)) => actions$.pipe(
    ofType(DeliveryActions.statusChangeRequested),
    exhaustMap(({ orderId, transition }) => {
      const request = transition === 'deliver' ? service.deliver(orderId) : service.undo(orderId);
      return request.pipe(
        map(({ status }) => DeliveryActions.statusChangeSuccess({ status })),
        catchError((error: HttpErrorResponse) => of(DeliveryActions.statusChangeFailure({ errorCode: errorCode(error) }))),
      );
    }),
  ),
  { functional: true },
);

export const deliveryEffects = { deliveryLoadEffect, deliveryStatusChangeEffect };
