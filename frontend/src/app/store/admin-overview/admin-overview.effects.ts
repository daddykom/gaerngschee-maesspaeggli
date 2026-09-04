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

export const adminOverviewEffects = {
  loadAdminOverviewEffect,
  adminOverviewNotificationEffect,
};
