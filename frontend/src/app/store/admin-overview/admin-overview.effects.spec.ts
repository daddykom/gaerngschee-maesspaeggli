import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, Subject } from 'rxjs';
import { EventActions } from '../events/events.actions';
import { AdminOverviewActions } from './admin-overview.actions';
import { reloadAdminOverviewAfterOrderStatusChangeEffect } from './admin-overview.effects';

describe('admin overview effects', () => {
  it('reloads after an order status change event', async () => {
    const actions$ = new Subject<Action>();
    TestBed.configureTestingModule({ providers: [provideMockActions(() => actions$)] });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => reloadAdminOverviewAfterOrderStatusChangeEffect()));

    actions$.next(EventActions.received({ key: 'order-status-change', value: 1 }));

    await expect(result).resolves.toEqual(AdminOverviewActions.load());
  });

  it('ignores other event keys', async () => {
    const actions$ = new Subject<Action>();
    TestBed.configureTestingModule({ providers: [provideMockActions(() => actions$)] });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => reloadAdminOverviewAfterOrderStatusChangeEffect()));

    actions$.next(EventActions.received({ key: 'other-event', value: 1 }));
    actions$.next(EventActions.received({ key: 'order-status-change', value: 2 }));

    await expect(result).resolves.toEqual(AdminOverviewActions.load());
  });
});
