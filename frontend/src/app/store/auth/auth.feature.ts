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
    on(AuthActions.loginSuccess, (state, { token, group }) => ({
      ...state,
      token,
      group,
      loading: false,
      errorCode: null,
    })),
    on(AuthActions.loginFailure, (state, { errorCode }) => ({
      ...state,
      token: null,
      group: null,
      loading: false,
      errorCode,
    })),
    on(AuthActions.logout, () => initialState),
  ),
});

export const {
  name: authFeatureName,
  reducer: authReducer,
  selectAuthState,
  selectToken: selectAuthToken,
  selectGroup: selectAuthGroup,
  selectLoading: selectAuthLoading,
  selectErrorCode: selectAuthErrorCode,
} = authFeature;
