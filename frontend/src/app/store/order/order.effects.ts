import { HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of } from 'rxjs';
import { OrderService } from '../../shared/services/order.service';
import { NotificationActions } from '../notification/notification.actions';
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

export const saveCurrentOrderEffect = createEffect(
  (actions$ = inject(Actions), service = inject(OrderService)) => actions$.pipe(
    ofType(OrderActions.save),
    exhaustMap(({ draft }) => service.saveCurrent(draft).pipe(
      map(({ order }) => order === null
        ? OrderActions.saveFailure({ errorCode: 'ORDER_SAVE_FAILED' })
        : OrderActions.saveSuccess({ order })),
      catchError((error: HttpErrorResponse) => of(OrderActions.saveFailure({ errorCode: errorCode(error) }))),
    )),
  ),
  { functional: true },
);

export const orderNotificationEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(OrderActions.saveSuccess, OrderActions.saveFailure),
    map((action) => action.type === OrderActions.saveSuccess.type
      ? NotificationActions.show({
        variant: 'success',
        titleKey: 'app.order.notifications.successTitle',
        messageKey: 'app.order.notifications.success',
        preserveOnRoutes: ['/order/summary'],
      })
      : NotificationActions.show({
        variant: 'error',
        titleKey: 'app.order.notifications.errorTitle',
        messageKey: 'app.order.notifications.errors.REQUEST_FAILED',
        preserveOnRoutes: ['/order/summary'],
      })),
  ),
  { functional: true },
);

export const orderEffects = { loadCurrentOrderEffect, saveCurrentOrderEffect, orderNotificationEffect };
