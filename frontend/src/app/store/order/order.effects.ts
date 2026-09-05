import { HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of, withLatestFrom } from 'rxjs';
import { Store } from '@ngrx/store';
import { selectAuthAdultsCount, selectAuthChildrenCount } from '../auth/auth.feature';
import { ClientOrder, OrderForm } from '../../shared/models/order.model';
import { selectOrderForm } from './order.feature';
import { OrderService } from '../../shared/services/order.service';
import { NotificationActions } from '../notification/notification.actions';
import { AuthActions } from '../auth/auth.actions';
import { OrderActions } from './order.actions';

const errorCode = (error: HttpErrorResponse): string =>
  typeof error.error?.error?.code === 'string' ? error.error.error.code : 'REQUEST_FAILED';

export const loadCurrentOrderEffect = createEffect(
  (actions$ = inject(Actions), service = inject(OrderService), store = inject(Store)) => actions$.pipe(
    ofType(OrderActions.orderLoadRequested),
    withLatestFrom(store.select(selectAuthAdultsCount), store.select(selectAuthChildrenCount)),
    exhaustMap(([, adultsCount, childrenCount]) => service.getCurrent().pipe(
      map(({ order }) => OrderActions.orderLoaded({
        order,
        form: orderForm(order, adultsCount ?? 0, childrenCount ?? 0),
      })),
      catchError((error: HttpErrorResponse) => of(OrderActions.orderLoadFailed({ errorCode: errorCode(error) }))),
    )),
  ),
  { functional: true },
);

export const saveCurrentOrderEffect = createEffect(
  (actions$ = inject(Actions), service = inject(OrderService), store = inject(Store)) => actions$.pipe(
    ofType(OrderActions.orderSaveRequested),
    withLatestFrom(store.select(selectOrderForm)),
    exhaustMap(([, form]) => form === null
      ? of(OrderActions.orderSaveFailed({ errorCode: 'ORDER_SAVE_FAILED' }))
      : service.saveCurrent(form).pipe(
        map(({ order }) => order === null
          ? OrderActions.orderSaveFailed({ errorCode: 'ORDER_SAVE_FAILED' })
          : OrderActions.orderSaved({ order })),
        catchError((error: HttpErrorResponse) => of(OrderActions.orderSaveFailed({ errorCode: errorCode(error) }))),
      )),
  ),
  { functional: true },
);

export const orderNotificationEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(OrderActions.orderSaved, OrderActions.orderSaveFailed, OrderActions.orderLoadFailed),
    map((action) => action.type === OrderActions.orderSaved.type
      ? NotificationActions.show({
        variant: 'success',
        titleKey: 'app.order.notifications.successTitle',
        messageKey: 'app.order.notifications.success',
         preserveOnRoutes: ['/start'],
      })
      : action.type === OrderActions.orderLoadFailed.type
        ? NotificationActions.show({
          variant: 'error',
          titleKey: 'app.order.notifications.loadErrorTitle',
          messageKey: 'app.order.notifications.errors.REQUEST_FAILED',
          preserveOnRoutes: ['/order'],
        })
      : NotificationActions.show({
        variant: 'error',
        titleKey: 'app.order.notifications.errorTitle',
        messageKey: 'app.order.notifications.errors.REQUEST_FAILED',
        preserveOnRoutes: ['/order'],
      })),
  ),
  { functional: true },
);

export const orderLogoutEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(OrderActions.orderSaved),
    map(() => AuthActions.logoutRequested({ redirectTo: '/start' })),
  ),
  { functional: true },
);

export const orderEffects = { loadCurrentOrderEffect, saveCurrentOrderEffect, orderNotificationEffect, orderLogoutEffect };

const orderForm = (order: ClientOrder | null, adultsCount: number, childrenCount: number): OrderForm => ({
  adultsCount: order?.adultsCount ?? adultsCount,
  childrenCount: order?.childrenCount ?? childrenCount,
  adults: categoriesFor(order, 'adult', order?.adultsCount ?? adultsCount),
  children: categoriesFor(order, 'child', order?.childrenCount ?? childrenCount),
});

const categoriesFor = (order: ClientOrder | null, personType: 'adult' | 'child', count: number): (OrderForm['adults'][number])[] => order === null
  ? Array.from({ length: count }, () => '')
  : order.items
    .filter((item) => item.personType === personType)
    .flatMap((item) => Array.from({ length: item.quantity }, () => item.category))
    .slice(0, count);
