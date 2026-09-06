import { createFeature, createReducer, on } from '@ngrx/store';
import { OrderActions } from './order.actions';
import { initialState, OrderState } from './order.state';

export const orderFeature = createFeature({
  name: 'order',
  reducer: createReducer<OrderState>(
    initialState,
    on(OrderActions.orderLoadRequested, () => ({ status: 'loading' })),
    on(OrderActions.orderLoaded, (_, { order, form }) => ({ status: 'loaded', order, form })),
    on(OrderActions.orderLoadFailed, (_, { errorCode }) => ({ status: 'error', errorCode })),
    on(OrderActions.orderFormUpdated, (state, { form }) => state.status === 'loaded'
      ? { ...state, form: { ...state.form, ...form } }
      : state),
    on(OrderActions.orderSaved, (state, { order }) => state.status === 'loaded'
      ? { ...state, order }
      : state),
  ),
});

export const {
  name: orderFeatureName,
  reducer: orderReducer,
  selectOrderState,
} = orderFeature;

export const selectOrderStatus = (state: { order: OrderState }): OrderState['status'] => state.order.status;
export const selectCurrentOrder = (state: { order: OrderState }) =>
  state.order.status === 'loaded' ? state.order.order : null;
export const selectOrderForm = (state: { order: OrderState }) =>
  state.order.status === 'loaded' ? state.order.form : null;
