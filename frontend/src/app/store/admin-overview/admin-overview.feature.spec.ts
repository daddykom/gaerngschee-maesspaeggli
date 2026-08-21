import {
  adminOverviewFeature,
  selectAdminOverviewKategories,
  selectAdminOverviewNumbOrders,
} from './admin-overview.feature';
import { initialState } from './admin-overview.state';

describe('adminOverviewFeature', () => {
  it('has the expected initial state', () => {
    expect(adminOverviewFeature.reducer(undefined, { type: '@@init' })).toEqual(initialState);
  });

  it('exposes selectors for the overview values', () => {
    const state = {
      adminOverview: {
        numbOrders: 4,
        kategories: [{ kategoryId: 'standard', numbPackages: 12 }],
      },
    };

    expect(selectAdminOverviewNumbOrders(state)).toBe(4);
    expect(selectAdminOverviewKategories(state)).toEqual([
      { kategoryId: 'standard', numbPackages: 12 },
    ]);
  });
});
