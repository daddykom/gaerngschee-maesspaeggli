import { HttpErrorResponse } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject, throwError } from 'rxjs';
import { FrontendConfigService } from '../../shared/services/frontend-config.service';
import { NotificationActions } from '../notification/notification.actions';
import { FrontendConfigActions } from './frontend-config.actions';
import { frontendConfigNotificationEffect, loadFrontendConfigEffect, saveFrontendConfigEffect } from './frontend-config.effects';

describe('frontend config effects', () => {
  let actions$: Subject<Action>;
  let service: { list: jest.Mock; update: jest.Mock };

  beforeEach(() => {
    actions$ = new Subject<Action>();
    service = { list: jest.fn(), update: jest.fn() };
    TestBed.configureTestingModule({ providers: [provideMockActions(() => actions$), { provide: FrontendConfigService, useValue: service }] });
  });

  it('loads and saves configuration values', async () => {
    const configs = [{ id: 'site_name' } as never];
    service.list.mockReturnValue(of(configs));
    const load = firstValueFrom(TestBed.runInInjectionContext(() => loadFrontendConfigEffect()));
    actions$.next(FrontendConfigActions.load());
    await expect(load).resolves.toEqual(FrontendConfigActions.loadSuccess({ configs }));

    service.update.mockImplementation((id: string, value: string | string[]) => of({ id, value } as never));
    const save = firstValueFrom(TestBed.runInInjectionContext(() => saveFrontendConfigEffect()));
    actions$.next(FrontendConfigActions.save({ configs: [{ id: 'site_name', value: 'Gaerngschee' }, { id: 'languages', value: ['de', 'fr'] }] }));
    await expect(save).resolves.toEqual(FrontendConfigActions.saveSuccess({ configs: [{ id: 'site_name', value: 'Gaerngschee' }, { id: 'languages', value: ['de', 'fr'] }] as never }));
    expect(service.update).toHaveBeenNthCalledWith(1, 'site_name', 'Gaerngschee');
    expect(service.update).toHaveBeenNthCalledWith(2, 'languages', ['de', 'fr']);
  });

  it('maps backend errors and emits notifications', async () => {
    service.list.mockReturnValue(throwError(() => new HttpErrorResponse({ error: { error: { code: 'DENIED' } } })));
    const failure = firstValueFrom(TestBed.runInInjectionContext(() => loadFrontendConfigEffect()));
    actions$.next(FrontendConfigActions.load());
    await expect(failure).resolves.toEqual(FrontendConfigActions.loadFailure({ errorCode: 'DENIED' }));

    const notification = firstValueFrom(TestBed.runInInjectionContext(() => frontendConfigNotificationEffect()));
    actions$.next(FrontendConfigActions.saveFailure({ errorCode: 'INVALID' }));
    await expect(notification).resolves.toEqual(NotificationActions.show({ variant: 'error', titleKey: 'app.admin.configuration.errorTitle', messageKey: 'app.admin.configuration.errors.INVALID', preserveOnRoutes: ['/admin/configuration'] }));
  });
});
