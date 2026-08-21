import { HttpErrorResponse } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject, throwError } from 'rxjs';
import { AuthService } from '../../shared/services/auth.service';
import { AuthActions } from './auth.actions';
import { loginEffect } from './auth.effects';

describe('loginEffect', () => {
  let actions$: Subject<Action>;
  let authService: { login: jest.Mock };

  beforeEach(() => {
    actions$ = new Subject<Action>();
    authService = { login: jest.fn() };

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
});
