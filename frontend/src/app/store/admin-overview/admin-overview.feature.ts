import { createFeature, createReducer, on } from '@ngrx/store';
import { AdminOverviewActions } from './admin-overview.actions';
import { AdminOverviewState, initialState } from './admin-overview.state';

export const adminOverviewFeature = createFeature({
  name: 'adminOverview',
  reducer: createReducer<AdminOverviewState>(
    initialState,
    on(AdminOverviewActions.load, () => ({ status: 'loading' })),
    on(AdminOverviewActions.loadSuccess, (state, { overview }) => ({ status: 'loaded', overview })),
    on(AdminOverviewActions.loadFailure, (state, { errorCode }) => ({ status: 'error', errorCode })),
  ),
});

export const {
  name: adminOverviewFeatureName,
  reducer: adminOverviewReducer,
  selectAdminOverviewState,
} = adminOverviewFeature;

export const selectAdminOverviewStatus = (state: { adminOverview: AdminOverviewState }): AdminOverviewState['status'] =>
  state.adminOverview.status;

export const selectAdminOverview = (state: { adminOverview: AdminOverviewState }) =>
  state.adminOverview.status === 'loaded' ? state.adminOverview.overview : null;
