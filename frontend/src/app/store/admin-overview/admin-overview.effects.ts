import { HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of } from 'rxjs';
import { AdminOverviewService } from '../../shared/services/admin-overview.service';
import { NotificationActions } from '../notification/notification.actions';
import { AdminOverviewActions } from './admin-overview.actions';

const errorCode = (error: HttpErrorResponse): string =>
  typeof error.error?.error?.code === 'string' ? error.error.error.code : 'REQUEST_FAILED';

export const loadAdminOverviewEffect = createEffect(
  (actions$ = inject(Actions), service = inject(AdminOverviewService)) => actions$.pipe(
    ofType(AdminOverviewActions.load),
    exhaustMap(() => service.get().pipe(
      map((overview) => AdminOverviewActions.loadSuccess({ overview })),
      catchError((error: HttpErrorResponse) => of(AdminOverviewActions.loadFailure({ errorCode: errorCode(error) }))),
    )),
  ),
  { functional: true },
);

export const adminOverviewNotificationEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(AdminOverviewActions.loadFailure),
    map((action) => NotificationActions.show({
      variant: 'error',
      titleKey: 'app.admin.overview.errorTitle',
      messageKey: `app.admin.overview.errors.${action.errorCode}`,
      preserveOnRoutes: ['/admin/overview'],
    })),
  ),
  { functional: true },
);

export const deliverAdminOverviewEffect = createEffect(
  (actions$ = inject(Actions), service = inject(AdminOverviewService)) => actions$.pipe(
    ofType(AdminOverviewActions.deliver),
    exhaustMap(() => service.deliver().pipe(
      map(({ updated }) => AdminOverviewActions.deliverSuccess({ updated })),
      catchError((error: HttpErrorResponse) => of(AdminOverviewActions.deliverFailure({ errorCode: errorCode(error) }))),
    )),
  ),
  { functional: true },
);

export const deliverAdminOverviewNotificationEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(AdminOverviewActions.deliverSuccess, AdminOverviewActions.deliverFailure),
    map((action) => action.type === AdminOverviewActions.deliverSuccess.type
      ? NotificationActions.show({
        variant: 'success',
        titleKey: 'app.admin.overview.successTitle',
        messageKey: 'app.admin.overview.deliveredMessage',
        params: { updated: `${action.updated}` },
        preserveOnRoutes: ['/admin/overview'],
      })
      : NotificationActions.show({
        variant: 'error',
        titleKey: 'app.admin.overview.errorTitle',
        messageKey: `app.admin.overview.errors.${action.errorCode}`,
        preserveOnRoutes: ['/admin/overview'],
      })),
  ),
  { functional: true },
);

export const reloadAdminOverviewAfterDeliveryEffect = createEffect(
  (actions$ = inject(Actions)) => actions$.pipe(
    ofType(AdminOverviewActions.deliverSuccess),
    map(() => AdminOverviewActions.load()),
  ),
  { functional: true },
);

export const adminOverviewEffects = {
  loadAdminOverviewEffect,
  adminOverviewNotificationEffect,
  deliverAdminOverviewEffect,
  deliverAdminOverviewNotificationEffect,
  reloadAdminOverviewAfterDeliveryEffect,
};
