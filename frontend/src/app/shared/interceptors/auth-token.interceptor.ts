import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Store } from '@ngrx/store';
import { selectAuthToken } from '../../store/auth/auth.feature';

export const authTokenInterceptor: HttpInterceptorFn = (request, next) => {
  const token = inject(Store).selectSignal(selectAuthToken)();
  const csrfToken = typeof document === 'undefined'
    ? null
    : document.cookie
      .split('; ')
      .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
      ?.split('=')[1] ?? null;

  const headers: Record<string, string> = {};
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }
  if (csrfToken) {
    headers['X-CSRF-Token'] = csrfToken;
  }

  return next(Object.keys(headers).length > 0 ? request.clone({ setHeaders: headers }) : request);
};
