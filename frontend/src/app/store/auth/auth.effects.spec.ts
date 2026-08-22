import { HttpErrorResponse } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject, throwError } from 'rxjs';
import { AuthService } from '../../shared/services/auth.service';
import { AuthActions } from './auth.actions';
import { NavigationActions } from '../navigation/navigation.actions';
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
      requiredPasswordReset: false,
    }));
    const effect$ = TestBed.runInInjectionContext(() => loginEffect());
    const result = firstValueFrom(effect$);

    actions$.next(AuthActions.login({ email: 'user@example.com', password: 'secret' }));

    await expect(result).resolves.toEqual(
      AuthActions.loginSuccess({
        token: 'jwt-token',
        userId: 'user-123',
        group: 'admin',
        requiredPasswordReset: false,
      }),
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

  it('dispatches navigation to the admin overview after a successful login', async () => {
    const effect$ = TestBed.runInInjectionContext(() => navigateOnLoginSuccessEffect());
    const result = firstValueFrom(effect$);

    actions$.next(AuthActions.loginSuccess({
      token: 'jwt-token',
      userId: 'user-123',
      group: 'admin',
      requiredPasswordReset: false,
    }));

    await expect(result).resolves.toEqual(NavigationActions.navigate({ target: '/admin/overview' }));
  });

  it('dispatches navigation to password change when a reset is required', async () => {
    const effect$ = TestBed.runInInjectionContext(() => navigateOnLoginSuccessEffect());
    const result = firstValueFrom(effect$);

    actions$.next(AuthActions.loginSuccess({
      token: 'jwt-token',
      userId: 'user-123',
      group: 'user',
      requiredPasswordReset: true,
    }));

    await expect(result).resolves.toEqual(NavigationActions.navigate({ target: '/password-change' }));
  });

  it('calls the backend logout and dispatches navigation to login after success', async () => {
    authService.logout.mockReturnValue(of(undefined));
    const effect$ = TestBed.runInInjectionContext(() => logoutEffect());
    const result = firstValueFrom(effect$);

    actions$.next(AuthActions.logout());

    expect(authService.logout).toHaveBeenCalledTimes(1);
    await expect(result).resolves.toEqual(NavigationActions.navigate({ target: '/login' }));
  });

  it('dispatches navigation to login when the backend logout fails', async () => {
    authService.logout.mockReturnValue(throwError(() => new Error('Logout failed')));
    const effect$ = TestBed.runInInjectionContext(() => logoutEffect());
    const result = firstValueFrom(effect$);

    actions$.next(AuthActions.logout());

    expect(authService.logout).toHaveBeenCalledTimes(1);
    await expect(result).resolves.toEqual(NavigationActions.navigate({ target: '/login' }));
  });
});
