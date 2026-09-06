import { AdminUsersActions } from './admin-users.actions';
import {
  adminUsersFeature,
  selectAdminUsers,
  selectAdminUsersLoading,
  selectAdminUsersSaving,
} from './admin-users.feature';
import { initialState } from './admin-users.state';

const user = {
  id: 'user-1',
  email: 'user@example.com',
  group: 'user' as const,
  required_password_reset: false,
  created_at: null,
  updated_at: null,
};

describe('adminUsersFeature', () => {
  it('has the expected initial state and selectors', () => {
    expect(adminUsersFeature.reducer(undefined, { type: '@@init' })).toEqual(initialState);
    const state = { adminUsers: { ...initialState, users: [user], loading: true, saving: true } };

    expect(selectAdminUsers(state)).toEqual([user]);
    expect(selectAdminUsersLoading(state)).toBe(true);
    expect(selectAdminUsersSaving(state)).toBe(true);
  });

  it('tracks loading and saving mutations', () => {
    expect(adminUsersFeature.reducer(initialState, AdminUsersActions.load).loading).toBe(true);
    expect(adminUsersFeature.reducer(initialState, AdminUsersActions.create({ email: user.email, group: 'user' })).saving).toBe(true);
    expect(adminUsersFeature.reducer(initialState, AdminUsersActions.update({ userId: user.id, changes: {} })).saving).toBe(true);
    expect(adminUsersFeature.reducer(initialState, AdminUsersActions.delete({ userId: user.id })).saving).toBe(true);
  });

  it('stores loaded, created, updated, and deleted users', () => {
    const updated = { ...user, id: 'user-2', email: 'updated@example.com' };
    const loaded = adminUsersFeature.reducer(initialState, AdminUsersActions.loadSuccess({ users: [user] }));
    const created = adminUsersFeature.reducer(loaded, AdminUsersActions.createSuccess({ user: updated, emailSentTo: updated.email }));
    const changed = adminUsersFeature.reducer(created, AdminUsersActions.updateSuccess({ user: { ...updated, group: 'admin' }, emailSentTo: null }));
    const deleted = adminUsersFeature.reducer(changed, AdminUsersActions.deleteSuccess({ userId: updated.id }));

    expect(loaded.users).toEqual([user]);
    expect(created.users).toEqual([updated, user]);
    expect(changed.users[0].group).toBe('admin');
    expect(deleted.users).toEqual([user]);
  });

  it('stops loading or saving after failures', () => {
    const loading = adminUsersFeature.reducer({ ...initialState, loading: true }, AdminUsersActions.loadFailure({ errorCode: 'FAILED' }));
    const saving = adminUsersFeature.reducer({ ...initialState, saving: true }, AdminUsersActions.deleteFailure({ errorCode: 'FAILED' }));

    expect(loading.loading).toBe(false);
    expect(saving.saving).toBe(false);
  });
});
