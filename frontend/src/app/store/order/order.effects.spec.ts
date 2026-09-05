import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject } from 'rxjs';
import { OrderService } from '../../shared/services/order.service';
import { provideMockStore } from '@ngrx/store/testing';
import { initialState as authInitialState } from '../auth/auth.state';
import { OrderActions } from './order.actions';
import { loadCurrentOrderEffect } from './order.effects';
import { orderLogoutEffect } from './order.effects';
import { AuthActions } from '../auth/auth.actions';

describe('order effects', () => {
  it('loads the current order', async () => {
    const actions$ = new Subject<Action>();
    const order = null;
    const service = { getCurrent: jest.fn(() => of({ order })) };
    TestBed.configureTestingModule({
      providers: [provideMockActions(() => actions$), { provide: OrderService, useValue: service }, provideMockStore({ initialState: { auth: authInitialState } })],
    });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => loadCurrentOrderEffect()));

    actions$.next(OrderActions.orderLoadRequested());

    await expect(result).resolves.toEqual(OrderActions.orderLoaded({ order, form: { adultsCount: 0, childrenCount: 0, adults: [], children: [] } }));
    expect(service.getCurrent).toHaveBeenCalledTimes(1);
  });

  it('logs the client out and redirects to the start page after saving', async () => {
    const actions$ = new Subject<Action>();
    TestBed.configureTestingModule({ providers: [provideMockActions(() => actions$)] });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => orderLogoutEffect()));

    actions$.next(OrderActions.orderSaved({ order: {} as never }));

    await expect(result).resolves.toEqual(AuthActions.logoutRequested({ redirectTo: '/start' }));
  });
});
