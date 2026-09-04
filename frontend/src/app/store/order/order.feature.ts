import { createFeature, createReducer, on } from '@ngrx/store';
import { OrderActions } from './order.actions';
import { initialState, OrderState } from './order.state';

export const orderFeature = createFeature({
  name: 'order',
  reducer: createReducer<OrderState>(
    initialState,
    on(OrderActions.loadCurrent, (state) => ({ ...state, loading: true, errorCode: null })),
    on(OrderActions.loadCurrentSuccess, (state, { order }) => ({ ...state, order, loading: false, loaded: true })),
    on(OrderActions.loadCurrentFailure, (state, { errorCode }) => ({ ...state, loading: false, loaded: true, errorCode })),
  ),
});

export const {
  name: orderFeatureName,
  reducer: orderReducer,
  selectOrder: selectCurrentOrder,
  selectLoading: selectOrderLoading,
  selectLoaded: selectOrderLoaded,
  selectErrorCode: selectOrderErrorCode,
} = orderFeature;
