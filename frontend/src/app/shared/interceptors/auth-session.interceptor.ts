import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Store } from '@ngrx/store';
import { catchError, throwError } from 'rxjs';
import { SessionActions } from '../../store/auth/session.actions';

export const authSessionInterceptor: HttpInterceptorFn = (request, next) => {
  const store = inject(Store);

  return next(request).pipe(
    catchError((error: HttpErrorResponse) => {
      if (error.status === 401 && error.error?.error?.code === 'SESSION_EXPIRED') {
        store.dispatch(SessionActions.sessionExpired());
      }

      return throwError(() => error);
    }),
  );
};
