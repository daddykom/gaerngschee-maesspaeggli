import { HttpErrorResponse } from '@angular/common/http';
import { Location } from '@angular/common';
import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { catchError, exhaustMap, map, of, tap } from 'rxjs';
import { AdminUsersService } from '../../shared/services/admin-users.service';
import { AdminUsersActions } from './admin-users.actions';

const errorCode = (error: HttpErrorResponse): string =>
  typeof error.error?.error?.code === 'string' ? error.error.error.code : 'REQUEST_FAILED';

export const loadAdminUsersEffect = createEffect(
  (actions$ = inject(Actions), service = inject(AdminUsersService)) => actions$.pipe(
    ofType(AdminUsersActions.load),
    exhaustMap(() => service.list().pipe(
      map((users) => AdminUsersActions.loadSuccess({ users })),
      catchError((error: HttpErrorResponse) => of(AdminUsersActions.loadFailure({ errorCode: errorCode(error) }))),
    )),
  ),
  { functional: true },
);

export const createAdminUserEffect = createEffect(
  (actions$ = inject(Actions), service = inject(AdminUsersService)) => actions$.pipe(
    ofType(AdminUsersActions.create),
    exhaustMap(({ email, group }) => service.create(email, group).pipe(
      map(({ user, emailSentTo }) => AdminUsersActions.createSuccess({
        user,
        emailSentTo: emailSentTo ?? user.email,
      })),
      catchError((error: HttpErrorResponse) => of(AdminUsersActions.createFailure({ errorCode: errorCode(error) }))),
    )),
  ),
  { functional: true },
);

export const updateAdminUserEffect = createEffect(
  (actions$ = inject(Actions), service = inject(AdminUsersService)) => actions$.pipe(
    ofType(AdminUsersActions.update),
    exhaustMap(({ userId, changes }) => service.update(userId, changes).pipe(
      map(({ user, emailSentTo }) => AdminUsersActions.updateSuccess({ user, emailSentTo: emailSentTo ?? null })),
      catchError((error: HttpErrorResponse) => of(AdminUsersActions.updateFailure({ errorCode: errorCode(error) }))),
    )),
  ),
  { functional: true },
);

export const deleteAdminUserEffect = createEffect(
  (actions$ = inject(Actions), service = inject(AdminUsersService)) => actions$.pipe(
    ofType(AdminUsersActions.delete),
    exhaustMap(({ userId }) => service.delete(userId).pipe(
      map(() => AdminUsersActions.deleteSuccess({ userId })),
      catchError((error: HttpErrorResponse) => of(AdminUsersActions.deleteFailure({ errorCode: errorCode(error) }))),
    )),
  ),
  { functional: true },
);

export const navigateBackAfterUserMutationEffect = createEffect(
  (actions$ = inject(Actions), location = inject(Location)) => actions$.pipe(
    ofType(AdminUsersActions.createSuccess, AdminUsersActions.updateSuccess),
    tap(() => location.back()),
  ),
  { functional: true, dispatch: false },
);

export const adminUsersEffects = {
  loadAdminUsersEffect,
  createAdminUserEffect,
  updateAdminUserEffect,
  deleteAdminUserEffect,
  navigateBackAfterUserMutationEffect,
};
