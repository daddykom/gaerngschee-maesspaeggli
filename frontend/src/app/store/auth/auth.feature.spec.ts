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
      AuthActions.logout(),
    );

    expect(state).toEqual(initialState);
  });
});
