import { createFeature, createReducer, on } from '@ngrx/store';
import { AdminUsersActions } from './admin-users.actions';
import { AdminUsersState, initialState } from './admin-users.state';

export const adminUsersFeature = createFeature({
  name: 'adminUsers',
  reducer: createReducer<AdminUsersState>(
    initialState,
    on(AdminUsersActions.load, (state) => ({
      ...state,
      loading: true,
      errorCode: null,
    })),
    on(AdminUsersActions.loadSuccess, (state, { users }) => ({
      ...state,
      users,
      loading: false,
    })),
    on(AdminUsersActions.loadFailure, (state, { errorCode }) => ({
      ...state,
      loading: false,
      errorCode,
    })),
    on(AdminUsersActions.create, AdminUsersActions.update, AdminUsersActions.delete, (state) => ({
      ...state,
      saving: true,
      errorCode: null,
      success: null,
      emailSentTo: null,
    })),
    on(AdminUsersActions.createSuccess, (state, { user, emailSentTo }) => ({
      ...state,
      users: [user, ...state.users],
      saving: false,
      success: 'created' as const,
      emailSentTo,
    })),
    on(AdminUsersActions.updateSuccess, (state, { user, emailSentTo }) => ({
      ...state,
      users: state.users.map((current) => current.id === user.id ? user : current),
      saving: false,
      success: 'updated' as const,
      emailSentTo,
    })),
    on(AdminUsersActions.deleteSuccess, (state, { userId }) => ({
      ...state,
      users: state.users.filter((user) => user.id !== userId),
      saving: false,
      success: 'deleted' as const,
    })),
    on(AdminUsersActions.createFailure, AdminUsersActions.updateFailure, AdminUsersActions.deleteFailure, (state, { errorCode }) => ({
      ...state,
      saving: false,
      errorCode,
    })),
    on(AdminUsersActions.clearFeedback, (state) => ({
      ...state,
      success: null,
      emailSentTo: null,
      errorCode: null,
    })),
  ),
});

export const {
  name: adminUsersFeatureName,
  reducer: adminUsersReducer,
  selectUsers: selectAdminUsers,
  selectLoading: selectAdminUsersLoading,
  selectSaving: selectAdminUsersSaving,
  selectErrorCode: selectAdminUsersErrorCode,
  selectSuccess: selectAdminUsersSuccess,
  selectEmailSentTo: selectAdminUsersEmailSentTo,
} = adminUsersFeature;
