import { createFeature, createReducer } from '@ngrx/store';
import { AdminOverviewState, initialState } from './admin-overview.state';

export const adminOverviewFeature = createFeature({
  name: 'adminOverview',
  reducer: createReducer<AdminOverviewState>(initialState),
});

export const {
  name: adminOverviewFeatureName,
  reducer: adminOverviewReducer,
  selectAdminOverviewState,
  selectNumbOrders: selectAdminOverviewNumbOrders,
  selectKategories: selectAdminOverviewKategories,
} = adminOverviewFeature;
