import { HttpErrorResponse } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject, throwError } from 'rxjs';
import { AuthService } from '../../shared/services/auth.service';
import { AuthActions } from './auth.actions';
import { NavigationActions } from '../navigation/navigation.actions';
import { NotificationActions } from '../notification/notification.actions';
import * as authStorage from '../../shared/services/auth-storage';
import {
  authNotificationEffect,
  clearPersistedAuthEffect,
  loginEffect,
  logoutEffect,
  navigateOnLoginSuccessEffect,
  navigateOnPasswordChangeSuccessEffect,
  navigateOnRegistrationLoginSuccessEffect,
  passwordChangeEffect,
  persistLoginEffect,
  persistRegistrationLoginEffect,
  registrationLoginEffect,
} from './auth.effects';

describe('loginEffect', () => {
  let actions$: Subject<Action>;
  let authService: { login: jest.Mock; registrationLogin: jest.Mock; logout: jest.Mock; changePassword: jest.Mock };

  beforeEach(() => {
    actions$ = new Subject<Action>();
    authService = { login: jest.fn(), registrationLogin: jest.fn(), logout: jest.fn(), changePassword: jest.fn() };

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

  it('uses the fallback error code for an unstructured login error', async () => {
    authService.login.mockReturnValue(throwError(() => new Error('failed')));
    const result = firstValueFrom(TestBed.runInInjectionContext(() => loginEffect()));

    actions$.next(AuthActions.login({ email: 'user@example.com', password: 'wrong' }));

    await expect(result).resolves.toEqual(AuthActions.loginFailure({ errorCode: 'LOGIN_FAILED' }));
  });

  it('maps registration login success and failure', async () => {
    authService.registrationLogin.mockReturnValue(of({
      token: 'client-token', user: { id: 'client-1', email: 'client@example.com', group: 'client' },
      group: 'client', requiredPasswordReset: false, fairgateUserExists: true,
      childrenCount: 2, adultsCount: 2, salutation: 'Hallo',
    }));
    const success = firstValueFrom(TestBed.runInInjectionContext(() => registrationLoginEffect()));
    actions$.next(AuthActions.registrationLogin({ token: 'registration-token' }));
    await expect(success).resolves.toEqual(AuthActions.registrationLoginSuccess({
      token: 'client-token', userId: 'client-1', group: 'client', fairgateUserExists: true,
      childrenCount: 2, adultsCount: 2, salutation: 'Hallo',
    }));
    expect(authService.registrationLogin).toHaveBeenCalledWith('registration-token');

    authService.registrationLogin.mockReturnValue(throwError(() => new Error('expired')));
    const failure = firstValueFrom(TestBed.runInInjectionContext(() => registrationLoginEffect()));
    actions$.next(AuthActions.registrationLogin({ token: 'expired-token' }));
    await expect(failure).resolves.toEqual(AuthActions.registrationLoginFailure({ errorCode: 'REGISTRATION_LOGIN_FAILED' }));
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

  it('navigates after registration login and password change', async () => {
    const registration = firstValueFrom(TestBed.runInInjectionContext(() => navigateOnRegistrationLoginSuccessEffect()));
    actions$.next(AuthActions.registrationLoginSuccess({
      token: 'token', userId: 'client-1', group: 'client', fairgateUserExists: false,
      childrenCount: 0, adultsCount: 1, salutation: 'Guten Tag',
    }));
    await expect(registration).resolves.toEqual(NavigationActions.navigate({ target: '/order' }));

    const password = firstValueFrom(TestBed.runInInjectionContext(() => navigateOnPasswordChangeSuccessEffect()));
    actions$.next(AuthActions.passwordChangeSuccess());
    await expect(password).resolves.toEqual(NavigationActions.navigate({ target: '/admin/overview' }));
  });

  it('maps password change success and errors', async () => {
    authService.changePassword.mockReturnValue(of({ user: { id: 'user-1' } }));
    const success = firstValueFrom(TestBed.runInInjectionContext(() => passwordChangeEffect()));
    actions$.next(AuthActions.passwordChange({ password: 'new-secret' }));
    await expect(success).resolves.toEqual(AuthActions.passwordChangeSuccess());
    expect(authService.changePassword).toHaveBeenCalledWith('new-secret');

    authService.changePassword.mockReturnValue(throwError(() => new Error('failed')));
    const failure = firstValueFrom(TestBed.runInInjectionContext(() => passwordChangeEffect()));
    actions$.next(AuthActions.passwordChange({ password: 'bad-secret' }));
    await expect(failure).resolves.toEqual(AuthActions.passwordChangeFailure({ errorCode: 'PASSWORD_CHANGE_FAILED' }));
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

  it('persists login state and clears it on logout', () => {
    const persist = jest.spyOn(authStorage, 'persistAuthState');
    const clear = jest.spyOn(authStorage, 'clearPersistedAuthState');
    const persistResult = TestBed.runInInjectionContext(() => persistLoginEffect());
    const clearResult = TestBed.runInInjectionContext(() => clearPersistedAuthEffect());

    const loginSubscription = persistResult.subscribe();
    actions$.next(AuthActions.loginSuccess({ token: 'token', userId: 'user-1', group: 'admin', requiredPasswordReset: false }));
    const logoutSubscription = clearResult.subscribe();
    actions$.next(AuthActions.logout());

    expect(persist).toHaveBeenCalledWith({ token: 'token', userId: 'user-1', group: 'admin', fairgateUserExists: null, childrenCount: null, adultsCount: null, salutation: null });
    expect(clear).toHaveBeenCalledTimes(1);
    loginSubscription.unsubscribe();
    logoutSubscription.unsubscribe();
  });

  it('persists registration data', () => {
    const persist = jest.spyOn(authStorage, 'persistAuthState');
    const effect$ = TestBed.runInInjectionContext(() => persistRegistrationLoginEffect());
    const subscription = effect$.subscribe();

    actions$.next(AuthActions.registrationLoginSuccess({
      token: 'token', userId: 'client-1', group: 'client', fairgateUserExists: true,
      childrenCount: 1, adultsCount: 2, salutation: 'Hallo',
    }));

    expect(persist).toHaveBeenCalledWith({ token: 'token', userId: 'client-1', group: 'client', fairgateUserExists: true, childrenCount: 1, adultsCount: 2, salutation: 'Hallo' });
    subscription.unsubscribe();
  });

  it('creates notifications for login and password failures', async () => {
    const effect$ = TestBed.runInInjectionContext(() => authNotificationEffect());
    const result = firstValueFrom(effect$);
    actions$.next(AuthActions.loginFailure({ errorCode: 'INVALID_CREDENTIALS' }));
    await expect(result).resolves.toEqual(NotificationActions.show({ variant: 'error', titleKey: 'app.auth.loginErrorTitle', messageKey: 'app.auth.errors.INVALID_CREDENTIALS' }));

    const passwordResult = firstValueFrom(effect$);
    actions$.next(AuthActions.passwordChangeFailure({ errorCode: 'WEAK_PASSWORD' }));
    await expect(passwordResult).resolves.toEqual(NotificationActions.show({ variant: 'error', titleKey: 'app.passwordChange.heading', messageKey: 'app.passwordChange.errors.WEAK_PASSWORD' }));
  });
});
