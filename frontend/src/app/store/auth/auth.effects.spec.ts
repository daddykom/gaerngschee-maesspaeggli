import { HttpErrorResponse } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject, throwError } from 'rxjs';
import { AuthService } from '../../shared/services/auth.service';
import { AuthActions } from './auth.actions';
import { loginEffect, logoutEffect, navigateOnLoginSuccessEffect } from './auth.effects';

describe('loginEffect', () => {
  let actions$: Subject<Action>;
  let authService: { login: jest.Mock; logout: jest.Mock };

  beforeEach(() => {
    actions$ = new Subject<Action>();
    authService = { login: jest.fn(), logout: jest.fn() };

    TestBed.configureTestingModule({
      providers: [
        provideMockActions(() => actions$),
        { provide: AuthService, useValue: authService },
      ],
    });
  });

  it('maps a successful login to a success action', async () => {
    authService.login.mockReturnValue(of({
      user: { id: 'user-123', email: 'user@example.com', group: 'admin' },
      token: 'jwt-token',
      group: 'admin',
    }));
    const effect$ = TestBed.runInInjectionContext(() => loginEffect());
    const result = firstValueFrom(effect$);

    actions$.next(AuthActions.login({ email: 'user@example.com', password: 'secret' }));

    await expect(result).resolves.toEqual(
      AuthActions.loginSuccess({ token: 'jwt-token', group: 'admin' }),
    );
    expect(authService.login).toHaveBeenCalledWith('user@example.com', 'secret');
  });

  it('maps a structured backend error to a failure action', async () => {
    authService.login.mockReturnValue(throwError(() => new HttpErrorResponse({
      status: 401,
      error: { error: { code: 'INVALID_CREDENTIALS', details: [] } },
    })));
    const effect$ = TestBed.runInInjectionContext(() => loginEffect());
    const result = firstValueFrom(effect$);

    actions$.next(AuthActions.login({ email: 'user@example.com', password: 'wrong' }));

    await expect(result).resolves.toEqual(
      AuthActions.loginFailure({ errorCode: 'INVALID_CREDENTIALS' }),
    );
  });

  it('navigates to the admin overview after a successful login', () => {
    const router = { navigateByUrl: jest.fn() };
    TestBed.overrideProvider(Router, { useValue: router });
    const effect$ = TestBed.runInInjectionContext(() => navigateOnLoginSuccessEffect());
    const subscription = effect$.subscribe();

    actions$.next(AuthActions.loginSuccess({ token: 'jwt-token', group: 'admin' }));

    expect(router.navigateByUrl).toHaveBeenCalledWith('/admin/overview');
    subscription.unsubscribe();
  });

  it('calls the backend logout and navigates to login after success', () => {
    const router = { navigateByUrl: jest.fn() };
    authService.logout.mockReturnValue(of(undefined));
    TestBed.overrideProvider(Router, { useValue: router });
    const effect$ = TestBed.runInInjectionContext(() => logoutEffect());
    const subscription = effect$.subscribe();

    actions$.next(AuthActions.logout());

    expect(authService.logout).toHaveBeenCalledTimes(1);
    expect(router.navigateByUrl).toHaveBeenCalledWith('/login');
    subscription.unsubscribe();
  });

  it('navigates to login when the backend logout fails', () => {
    const router = { navigateByUrl: jest.fn() };
    authService.logout.mockReturnValue(throwError(() => new Error('Logout failed')));
    TestBed.overrideProvider(Router, { useValue: router });
    const effect$ = TestBed.runInInjectionContext(() => logoutEffect());
    const subscription = effect$.subscribe({
      error: (error) => {
        throw error;
      },
    });

    actions$.next(AuthActions.logout());

    expect(authService.logout).toHaveBeenCalledTimes(1);
    expect(router.navigateByUrl).toHaveBeenCalledWith('/login');
    subscription.unsubscribe();
  });
});
