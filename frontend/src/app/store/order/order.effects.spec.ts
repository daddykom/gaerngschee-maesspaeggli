import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject } from 'rxjs';
import { OrderService } from '../../shared/services/order.service';
import { OrderActions } from './order.actions';
import { loadCurrentOrderEffect } from './order.effects';

describe('order effects', () => {
  it('loads the current order', async () => {
    const actions$ = new Subject<Action>();
    const order = null;
    const service = { getCurrent: jest.fn(() => of({ order })) };
    TestBed.configureTestingModule({
      providers: [provideMockActions(() => actions$), { provide: OrderService, useValue: service }],
    });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => loadCurrentOrderEffect()));

    actions$.next(OrderActions.loadCurrent());

    await expect(result).resolves.toEqual(OrderActions.loadCurrentSuccess({ order }));
    expect(service.getCurrent).toHaveBeenCalledTimes(1);
  });
});
