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
    on(OrderActions.setDraft, (state, { draft }) => ({ ...state, draft })),
    on(OrderActions.save, (state, { draft }) => ({ ...state, draft, saving: true, errorCode: null })),
    on(OrderActions.saveSuccess, (state, { order }) => ({ ...state, order, saving: false })),
    on(OrderActions.saveFailure, (state, { errorCode }) => ({ ...state, saving: false, errorCode })),
  ),
});

export const {
  name: orderFeatureName,
  reducer: orderReducer,
  selectOrder: selectCurrentOrder,
  selectDraft: selectOrderDraft,
  selectLoading: selectOrderLoading,
  selectLoaded: selectOrderLoaded,
  selectSaving: selectOrderSaving,
  selectErrorCode: selectOrderErrorCode,
} = orderFeature;
