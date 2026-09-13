import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject, throwError } from 'rxjs';
import { OrderService } from '../../shared/services/order.service';
import { provideMockStore } from '@ngrx/store/testing';
import { initialState as authInitialState } from '../auth/auth.state';
import { OrderActions } from './order.actions';
import { loadCurrentOrderEffect, orderLogoutEffect, saveCurrentOrderEffect } from './order.effects';
import { AuthActions } from '../auth/auth.actions';
import { HttpErrorResponse } from '@angular/common/http';

describe('order effects', () => {
  it('loads the current order', async () => {
    const actions$ = new Subject<Action>();
    const order = null;
    const service = { getCurrent: vi.fn(() => of({ order })) };
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

  it('turns a load error into an order load failure', async () => {
    const actions$ = new Subject<Action>();
    const service = { getCurrent: vi.fn(() => throwError(() => new HttpErrorResponse({
      status: 503,
      error: { error: { code: 'ORDER_LOAD_FAILED' } },
    }))) };
    TestBed.configureTestingModule({
      providers: [provideMockActions(() => actions$), { provide: OrderService, useValue: service }, provideMockStore({ initialState: { auth: authInitialState } })],
    });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => loadCurrentOrderEffect()));

    actions$.next(OrderActions.orderLoadRequested());

    await expect(result).resolves.toEqual(OrderActions.orderLoadFailed({ errorCode: 'ORDER_LOAD_FAILED' }));
  });

  it('turns a save error into an order save failure', async () => {
    const actions$ = new Subject<Action>();
    const service = { saveCurrent: vi.fn(() => throwError(() => new HttpErrorResponse({
      status: 409,
      error: { error: { code: 'ORDER_LOCKED' } },
    }))) };
    TestBed.configureTestingModule({
      providers: [
        provideMockActions(() => actions$),
        { provide: OrderService, useValue: service },
        provideMockStore({ initialState: { order: { status: 'loaded', order: null, form: { adultsCount: 1, childrenCount: 0, adults: ['catA'], children: [] } } } }),
      ],
    });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => saveCurrentOrderEffect()));

    actions$.next(OrderActions.orderSaveRequested());

    await expect(result).resolves.toEqual(OrderActions.orderSaveFailed({ errorCode: 'ORDER_LOCKED' }));
  });

  it('fails saving when no order form is loaded', async () => {
    const actions$ = new Subject<Action>();
    const service = { saveCurrent: vi.fn() };
    TestBed.configureTestingModule({
      providers: [
        provideMockActions(() => actions$),
        { provide: OrderService, useValue: service },
        provideMockStore({ initialState: { order: { status: 'initial' } } }),
      ],
    });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => saveCurrentOrderEffect()));

    actions$.next(OrderActions.orderSaveRequested());

    await expect(result).resolves.toEqual(OrderActions.orderSaveFailed({ errorCode: 'ORDER_SAVE_FAILED' }));
    expect(service.saveCurrent).not.toHaveBeenCalled();
  });

  it('fails saving when the backend returns no order', async () => {
    const actions$ = new Subject<Action>();
    const service = { saveCurrent: vi.fn(() => of({ order: null })) };
    TestBed.configureTestingModule({
      providers: [
        provideMockActions(() => actions$),
        { provide: OrderService, useValue: service },
        provideMockStore({ initialState: { order: { status: 'loaded', order: null, form: { adultsCount: 1, childrenCount: 0, adults: ['catA'], children: [] } } } }),
      ],
    });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => saveCurrentOrderEffect()));

    actions$.next(OrderActions.orderSaveRequested());

    await expect(result).resolves.toEqual(OrderActions.orderSaveFailed({ errorCode: 'ORDER_SAVE_FAILED' }));
  });
});
