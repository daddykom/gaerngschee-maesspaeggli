import { HttpErrorResponse } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject, throwError } from 'rxjs';
import { AdminUsersService } from '../../shared/services/admin-users.service';
import { NavigationActions } from '../navigation/navigation.actions';
import { NotificationActions } from '../notification/notification.actions';
import { AdminUsersActions } from './admin-users.actions';
import {
  adminUsersNotificationEffect,
  createAdminUserEffect,
  deleteAdminUserEffect,
  loadAdminUsersEffect,
  navigateBackAfterUserMutationEffect,
  updateAdminUserEffect,
} from './admin-users.effects';

const user = { id: 'user-1', email: 'user@example.com', group: 'user' as const, required_password_reset: false, created_at: null, updated_at: null };

describe('admin users effects', () => {
  let actions$: Subject<Action>;
  let service: { list: jest.Mock; create: jest.Mock; update: jest.Mock; delete: jest.Mock };

  beforeEach(() => {
    actions$ = new Subject<Action>();
    service = { list: jest.fn(), create: jest.fn(), update: jest.fn(), delete: jest.fn() };
    TestBed.configureTestingModule({ providers: [provideMockActions(() => actions$), { provide: AdminUsersService, useValue: service }] });
  });

  it('loads users successfully and passes create/update/delete arguments', async () => {
    service.list.mockReturnValue(of([user]));
    const loadResult = firstValueFrom(TestBed.runInInjectionContext(() => loadAdminUsersEffect()));
    actions$.next(AdminUsersActions.load());
    await expect(loadResult).resolves.toEqual(AdminUsersActions.loadSuccess({ users: [user] }));

    service.create.mockReturnValue(of({ user, emailSentTo: undefined }));
    const createResult = firstValueFrom(TestBed.runInInjectionContext(() => createAdminUserEffect()));
    actions$.next(AdminUsersActions.create({ email: user.email, group: 'user' }));
    await expect(createResult).resolves.toEqual(AdminUsersActions.createSuccess({ user, emailSentTo: user.email }));
    expect(service.create).toHaveBeenCalledWith(user.email, 'user');

    service.update.mockReturnValue(of({ user, emailSentTo: 'new@example.com' }));
    const updateResult = firstValueFrom(TestBed.runInInjectionContext(() => updateAdminUserEffect()));
    actions$.next(AdminUsersActions.update({ userId: user.id, changes: { required_password_reset: true } }));
    await expect(updateResult).resolves.toEqual(AdminUsersActions.updateSuccess({ user, emailSentTo: 'new@example.com' }));
    expect(service.update).toHaveBeenCalledWith(user.id, { required_password_reset: true });

    service.delete.mockReturnValue(of({ deleted: true, userId: user.id }));
    const deleteResult = firstValueFrom(TestBed.runInInjectionContext(() => deleteAdminUserEffect()));
    actions$.next(AdminUsersActions.delete({ userId: user.id }));
    await expect(deleteResult).resolves.toEqual(AdminUsersActions.deleteSuccess({ userId: user.id }));
    expect(service.delete).toHaveBeenCalledWith(user.id);
  });

  it('maps structured and unstructured service errors to failures', async () => {
    service.list.mockReturnValue(throwError(() => new HttpErrorResponse({ error: { error: { code: 'DENIED' } } })));
    const result = firstValueFrom(TestBed.runInInjectionContext(() => loadAdminUsersEffect()));
    actions$.next(AdminUsersActions.load());
    await expect(result).resolves.toEqual(AdminUsersActions.loadFailure({ errorCode: 'DENIED' }));

    service.delete.mockReturnValue(throwError(() => new Error('failed')));
    const fallback = firstValueFrom(TestBed.runInInjectionContext(() => deleteAdminUserEffect()));
    actions$.next(AdminUsersActions.delete({ userId: user.id }));
    await expect(fallback).resolves.toEqual(AdminUsersActions.deleteFailure({ errorCode: 'REQUEST_FAILED' }));
  });

  it('navigates after create/update and creates success and failure notifications', async () => {
    const navigation = firstValueFrom(TestBed.runInInjectionContext(() => navigateBackAfterUserMutationEffect()));
    actions$.next(AdminUsersActions.updateSuccess({ user, emailSentTo: null }));
    await expect(navigation).resolves.toEqual(NavigationActions.navigate({ target: 'back' }));

    const notification = firstValueFrom(TestBed.runInInjectionContext(() => adminUsersNotificationEffect()));
    actions$.next(AdminUsersActions.updateSuccess({ user, emailSentTo: null }));
    await expect(notification).resolves.toEqual(NotificationActions.show({ variant: 'success', titleKey: 'app.admin.users.successTitle', messageKey: 'app.admin.users.updated', params: {}, preserveOnRoutes: ['/admin/users'] }));
  });
});
