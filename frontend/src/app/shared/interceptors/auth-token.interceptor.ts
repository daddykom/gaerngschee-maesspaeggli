import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Store } from '@ngrx/store';
import { selectAuthToken } from '../../store/auth/auth.feature';

export const authTokenInterceptor: HttpInterceptorFn = (request, next) => {
  const token = inject(Store).selectSignal(selectAuthToken)();

  return next(token
    ? request.clone({ setHeaders: { Authorization: `Bearer ${token}` } })
    : request);
};
