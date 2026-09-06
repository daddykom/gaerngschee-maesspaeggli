import { createFeature, createReducer, on } from '@ngrx/store';
import { DeliveryActions } from './delivery.actions';
import { DeliveryState, initialState } from './delivery.state';

export const deliveryFeature = createFeature({
  name: 'delivery',
  reducer: createReducer<DeliveryState>(
    initialState,
    on(DeliveryActions.emailChanged, (state, { email }) => ({ ...state, form: { email } })),
    on(DeliveryActions.loadRequested, (state, search) => ({ ...state, search, load: { status: 'loading' }, action: { status: 'initial' } })),
    on(DeliveryActions.loadSuccess, (state, { order, viaToken, clientName, children }) => ({
      ...state,
      load: { status: 'loaded' },
      action: { status: 'initial' },
      order,
      viaToken,
      clientName: clientName ?? null,
      children: children ?? [],
    })),
    on(DeliveryActions.loadFailure, (state, { errorCode }) => ({ ...state, load: { status: 'error', errorCode }, order: null, clientName: null, children: [] })),
    on(DeliveryActions.statusChangeRequested, (state) => ({ ...state, action: { status: 'loading' } })),
    on(DeliveryActions.statusChangeSuccess, (state, { status }) => ({
      ...state,
      action: { status: 'initial' },
      order: state.order ? { ...state.order, status } : null,
    })),
    on(DeliveryActions.statusChangeFailure, (state, { errorCode }) => ({ ...state, action: { status: 'error', errorCode } })),
  ),
});

export const {
  name: deliveryFeatureName,
  reducer: deliveryReducer,
  selectDeliveryState,
} = deliveryFeature;

export const selectDeliveryEmail = (state: { delivery: DeliveryState }) => state.delivery.form.email;
export const selectDeliveryOrder = (state: { delivery: DeliveryState }) => state.delivery.order;
export const selectDeliveryLoad = (state: { delivery: DeliveryState }) => state.delivery.load;
export const selectDeliveryAction = (state: { delivery: DeliveryState }) => state.delivery.action;
export const selectDeliveryViaToken = (state: { delivery: DeliveryState }) => state.delivery.viaToken;
export const selectDeliveryClientName = (state: { delivery: DeliveryState }) => state.delivery.clientName;
export const selectDeliveryChildren = (state: { delivery: DeliveryState }) => state.delivery.children;
