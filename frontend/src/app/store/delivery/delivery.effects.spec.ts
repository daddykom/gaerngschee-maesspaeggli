import { HttpErrorResponse } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';
import { provideMockActions } from '@ngrx/effects/testing';
import { Action } from '@ngrx/store';
import { firstValueFrom, of, Subject, throwError } from 'rxjs';
import { DeliveryService } from '../../shared/services/delivery.service';
import { DeliveryActions } from './delivery.actions';
import { deliveryLoadEffect, deliveryStatusChangeEffect } from './delivery.effects';

describe('delivery effects', () => {
  const response = {
    order: {
      id: 'order-1',
      userId: 'client-1',
      year: 2026,
      status: 'qrcode' as const,
      adultsCount: 1,
      childrenCount: 0,
      items: [],
      createdAt: null,
      updatedAt: null,
    },
    viaToken: false,
    clientName: null,
    children: [],
  };

  it('loads an order using the requested email and maps the response', async () => {
    const actions$ = new Subject<Action>();
    const service = { getOrder: vi.fn(() => of(response)) };
    TestBed.configureTestingModule({ providers: [provideMockActions(() => actions$), { provide: DeliveryService, useValue: service }] });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => deliveryLoadEffect()));

    actions$.next(DeliveryActions.loadRequested({ email: 'person@example.com' }));

    await expect(result).resolves.toEqual(DeliveryActions.loadSuccess(response));
    expect(service.getOrder).toHaveBeenCalledWith({ email: 'person@example.com', token: undefined });
  });

  it('maps load errors to a delivery load failure', async () => {
    const actions$ = new Subject<Action>();
    const service = { getOrder: vi.fn(() => throwError(() => new HttpErrorResponse({ error: { error: { code: 'ORDER_NOT_FOUND' } } }))) };
    TestBed.configureTestingModule({ providers: [provideMockActions(() => actions$), { provide: DeliveryService, useValue: service }] });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => deliveryLoadEffect()));

    actions$.next(DeliveryActions.loadRequested({ token: 'delivery-token' }));

    await expect(result).resolves.toEqual(DeliveryActions.loadFailure({ errorCode: 'ORDER_NOT_FOUND' }));
  });

  it('calls the deliver endpoint and maps the new status', async () => {
    const actions$ = new Subject<Action>();
    const service = { deliver: vi.fn(() => of({ status: 'delivered' as const })), undo: vi.fn() };
    TestBed.configureTestingModule({ providers: [provideMockActions(() => actions$), { provide: DeliveryService, useValue: service }] });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => deliveryStatusChangeEffect()));

    actions$.next(DeliveryActions.statusChangeRequested({ orderId: 'order-1', transition: 'deliver' }));

    await expect(result).resolves.toEqual(DeliveryActions.statusChangeSuccess({ status: 'delivered' }));
    expect(service.deliver).toHaveBeenCalledWith('order-1');
    expect(service.undo).not.toHaveBeenCalled();
  });

  it('calls the undo endpoint and maps status errors', async () => {
    const actions$ = new Subject<Action>();
    const service = { deliver: vi.fn(), undo: vi.fn(() => throwError(() => new HttpErrorResponse({ error: { error: { code: 'DELIVERY_STATUS_INVALID' } } }))) };
    TestBed.configureTestingModule({ providers: [provideMockActions(() => actions$), { provide: DeliveryService, useValue: service }] });
    const result = firstValueFrom(TestBed.runInInjectionContext(() => deliveryStatusChangeEffect()));

    actions$.next(DeliveryActions.statusChangeRequested({ orderId: 'order-1', transition: 'undo' }));

    await expect(result).resolves.toEqual(DeliveryActions.statusChangeFailure({ errorCode: 'DELIVERY_STATUS_INVALID' }));
    expect(service.undo).toHaveBeenCalledWith('order-1');
    expect(service.deliver).not.toHaveBeenCalled();
  });
});
