import { OrderActions } from './order.actions';
import { orderFeature } from './order.feature';
import { initialState } from './order.state';

describe('orderFeature', () => {
  it('tracks the loaded current order and form', () => {
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
    expect(orderFeature.reducer(initialState, OrderActions.orderLoadRequested())).toEqual({ status: 'loading' });
    expect(orderFeature.reducer({ status: 'loading' }, OrderActions.orderLoaded({
      order,
      form: { adultsCount: 1, childrenCount: 0, adults: [], children: [] },
    }))).toEqual({ status: 'loaded', order, form: { adultsCount: 1, childrenCount: 0, adults: [], children: [] } });
  });

  it('updates only the supplied top-level form fields', () => {
    const state = { status: 'loaded' as const, order: null, form: { adultsCount: 1, childrenCount: 0, adults: ['catA' as const], children: [] } };
    expect(orderFeature.reducer(state, OrderActions.orderFormUpdated({ form: { adultsCount: 2 } }))).toEqual({ ...state, form: { ...state.form, adultsCount: 2 } });
  });
});
