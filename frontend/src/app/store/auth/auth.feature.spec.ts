import { AuthActions } from './auth.actions';
import { authReducer } from './auth.feature';
import { initialState } from './auth.state';

describe('authReducer', () => {
  it('starts loading and clears the previous error on login', () => {
    const state = authReducer(
      { ...initialState, errorCode: 'INVALID_CREDENTIALS' },
      AuthActions.login({ email: 'user@example.com', password: 'secret' }),
    );

    expect(state).toEqual({
      token: null,
      userId: null,
      group: null,
      requiredPasswordReset: false,
      passwordChangeLoading: false,
      passwordChangeErrorCode: null,
      loading: true,
      errorCode: null,
      registrationLoginLoading: false,
      registrationLoginErrorCode: null,
      fairgateUserExists: null,
      childrenCount: null,
      adultsCount: null,
      salutation: null,
    });
  });

  it('stores the token and group after a successful login', () => {
    const state = authReducer(
      { ...initialState, loading: true },
      AuthActions.loginSuccess({
        token: 'jwt-token',
        userId: 'user-123',
        group: 'admin',
        requiredPasswordReset: false,
      }),
    );

    expect(state).toEqual({
      token: 'jwt-token',
      userId: 'user-123',
      group: 'admin',
      requiredPasswordReset: false,
      passwordChangeLoading: false,
      passwordChangeErrorCode: null,
      loading: false,
      errorCode: null,
      registrationLoginLoading: false,
      registrationLoginErrorCode: null,
      fairgateUserExists: null,
      childrenCount: null,
      adultsCount: null,
      salutation: null,
    });
  });

  it('stores the error code after a failed login', () => {
    const state = authReducer(
      { ...initialState, loading: true },
      AuthActions.loginFailure({ errorCode: 'INVALID_CREDENTIALS' }),
    );

    expect(state).toEqual({
      token: null,
      userId: null,
      group: null,
      requiredPasswordReset: false,
      passwordChangeLoading: false,
      passwordChangeErrorCode: null,
      loading: false,
      errorCode: 'INVALID_CREDENTIALS',
      registrationLoginLoading: false,
      registrationLoginErrorCode: null,
      fairgateUserExists: null,
      childrenCount: null,
      adultsCount: null,
      salutation: null,
    });
  });

  it('clears authentication data on logout', () => {
    const state = authReducer(
      {
        token: 'jwt-token',
        userId: 'user-123',
        group: 'admin',
        requiredPasswordReset: false,
        passwordChangeLoading: false,
        passwordChangeErrorCode: null,
        loading: false,
        errorCode: null,
        registrationLoginLoading: false,
        registrationLoginErrorCode: null,
        fairgateUserExists: null,
        childrenCount: null,
        adultsCount: null,
        salutation: null,
      },
      AuthActions.logoutRequested({ redirectTo: '/login' }),
    );

    expect(state).toEqual(initialState);
  });

  it('stores registration login data and resets its loading state', () => {
    const loading = authReducer(initialState, AuthActions.registrationLogin({ token: 'registration-token' }));
    expect(loading.registrationLoginLoading).toBe(true);
    expect(loading.registrationLoginErrorCode).toBeNull();

    const state = authReducer(loading, AuthActions.registrationLoginSuccess({
      token: 'client-token', userId: 'client-1', group: 'client', fairgateUserExists: true,
      childrenCount: 2, adultsCount: 2, salutation: 'Hallo',
    }));
    expect(state).toMatchObject({ token: 'client-token', userId: 'client-1', group: 'client', registrationLoginLoading: false, registrationLoginErrorCode: null, fairgateUserExists: true, childrenCount: 2, adultsCount: 2, salutation: 'Hallo' });
  });

  it('stores registration and password change errors', () => {
    const registration = authReducer({ ...initialState, registrationLoginLoading: true }, AuthActions.registrationLoginFailure({ errorCode: 'TOKEN_EXPIRED' }));
    expect(registration).toMatchObject({ token: null, userId: null, group: null, registrationLoginLoading: false, registrationLoginErrorCode: 'TOKEN_EXPIRED' });

    const changing = authReducer(initialState, AuthActions.passwordChange({ password: 'secret' }));
    expect(changing).toMatchObject({ passwordChangeLoading: true, passwordChangeErrorCode: null });
    const failed = authReducer(changing, AuthActions.passwordChangeFailure({ errorCode: 'WEAK_PASSWORD' }));
    expect(failed).toMatchObject({ passwordChangeLoading: false, passwordChangeErrorCode: 'WEAK_PASSWORD' });
    const changed = authReducer({ ...failed, requiredPasswordReset: true }, AuthActions.passwordChangeSuccess());
    expect(changed).toMatchObject({ requiredPasswordReset: false, passwordChangeLoading: false, passwordChangeErrorCode: null });
  });
});
