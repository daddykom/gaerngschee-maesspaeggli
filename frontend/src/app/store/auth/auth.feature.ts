import { createFeature, createReducer, on } from '@ngrx/store';
import { AuthActions } from './auth.actions';
import { AuthState, initialState } from './auth.state';

export const authFeature = createFeature({
  name: 'auth',
  reducer: createReducer<AuthState>(
    initialState,
    on(AuthActions.login, (state) => ({
      ...state,
      loading: true,
      errorCode: null,
    })),
    on(AuthActions.loginSuccess, (state, { token, userId, group, requiredPasswordReset }) => ({
      ...state,
      token,
      userId,
      group,
      requiredPasswordReset,
      loading: false,
      errorCode: null,
    })),
    on(AuthActions.loginFailure, (state, { errorCode }) => ({
      ...state,
      token: null,
      userId: null,
      group: null,
      requiredPasswordReset: false,
      loading: false,
      errorCode,
    })),
    on(AuthActions.passwordChangeSuccess, (state) => ({
      ...state,
      requiredPasswordReset: false,
      passwordChangeLoading: false,
      passwordChangeErrorCode: null,
    })),
    on(AuthActions.passwordChange, (state) => ({
      ...state,
      passwordChangeLoading: true,
      passwordChangeErrorCode: null,
    })),
    on(AuthActions.passwordChangeFailure, (state, { errorCode }) => ({
      ...state,
      passwordChangeLoading: false,
      passwordChangeErrorCode: errorCode,
    })),
    on(AuthActions.logout, () => initialState),
  ),
});

export const {
  name: authFeatureName,
  reducer: authReducer,
  selectAuthState,
  selectToken: selectAuthToken,
  selectUserId: selectAuthUserId,
  selectGroup: selectAuthGroup,
  selectRequiredPasswordReset: selectAuthRequiredPasswordReset,
  selectPasswordChangeLoading: selectAuthPasswordChangeLoading,
  selectPasswordChangeErrorCode: selectAuthPasswordChangeErrorCode,
  selectLoading: selectAuthLoading,
  selectErrorCode: selectAuthErrorCode,
} = authFeature;
