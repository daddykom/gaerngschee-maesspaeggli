import { DeliveryActions } from './delivery.actions';
import { deliveryFeature } from './delivery.feature';
import { DeliveryState, initialState } from './delivery.state';

describe('deliveryFeature', () => {
  const order = {
    id: 'order-1',
    userId: 'client-1',
    year: 2026,
    status: 'qrcode' as const,
    adultsCount: 1,
    childrenCount: 0,
    items: [],
    createdAt: null,
    updatedAt: null,
  };

  it('tracks the search and resets a previous action state when loading', () => {
    const state: DeliveryState = { ...initialState, action: { status: 'error', errorCode: 'DELIVERY_STATUS_INVALID' } };

    expect(deliveryFeature.reducer(state, DeliveryActions.loadRequested({ email: 'person@example.com' }))).toEqual({
      ...state,
      search: { email: 'person@example.com' },
      load: { status: 'loading' },
      action: { status: 'initial' },
    });
  });

  it('stores a loaded order and optional delivery metadata', () => {
    const result = deliveryFeature.reducer(initialState, DeliveryActions.loadSuccess({
      order,
      viaToken: true,
      clientName: 'Person Example',
      children: [{ name: 'Child', birthDate: null }],
    }));

    expect(result).toEqual({
      ...initialState,
      load: { status: 'loaded' },
      order,
      viaToken: true,
      clientName: 'Person Example',
      children: [{ name: 'Child', birthDate: null }],
    });
  });

  it('clears stale order data when loading fails', () => {
    const state: DeliveryState = {
      ...initialState,
      order,
      clientName: 'Person Example',
      children: [{ name: 'Child', birthDate: null }],
    };

    expect(deliveryFeature.reducer(state, DeliveryActions.loadFailure({ errorCode: 'ORDER_NOT_FOUND' }))).toEqual({
      ...state,
      load: { status: 'error', errorCode: 'ORDER_NOT_FOUND' },
      order: null,
      clientName: null,
      children: [],
    });
  });

  it('tracks status changes and updates the loaded order', () => {
    const loaded: DeliveryState = { ...initialState, load: { status: 'loaded' }, order };
    const loading = deliveryFeature.reducer(
      loaded,
      DeliveryActions.statusChangeRequested({ orderId: 'order-1', transition: 'deliver' }),
    );

    expect(loading.action).toEqual({ status: 'loading' });
    expect(deliveryFeature.reducer(loading, DeliveryActions.statusChangeSuccess({ status: 'delivered' }))).toEqual({
      ...loading,
      action: { status: 'initial' },
      order: { ...order, status: 'delivered' },
    });
  });

  it('keeps the order when a status change fails', () => {
    const state: DeliveryState = { ...initialState, load: { status: 'loaded' }, order };

    expect(deliveryFeature.reducer(state, DeliveryActions.statusChangeFailure({ errorCode: 'DELIVERY_STATUS_INVALID' }))).toEqual({
      ...state,
      action: { status: 'error', errorCode: 'DELIVERY_STATUS_INVALID' },
    });
  });
});
