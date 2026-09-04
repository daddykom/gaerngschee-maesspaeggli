import { OrderActions } from './order.actions';
import { orderFeature } from './order.feature';
import { initialState } from './order.state';

describe('orderFeature', () => {
  it('tracks loading and the loaded current order', () => {
    const order = {
      id: 'order-1',
      userId: 'client-1',
      year: 2026,
      status: 'provisional' as const,
      adultsCount: 1,
      childrenCount: 0,
      items: [],
      createdAt: null,
      updatedAt: null,
    };

    expect(orderFeature.reducer(undefined, { type: '@@init' })).toEqual(initialState);
    expect(orderFeature.reducer(initialState, OrderActions.loadCurrent()).loading).toBe(true);
    expect(orderFeature.reducer({ ...initialState, loading: true }, OrderActions.loadCurrentSuccess({ order }))).toEqual({
      order,
      loading: false,
      loaded: true,
      errorCode: null,
    });
  });
});
