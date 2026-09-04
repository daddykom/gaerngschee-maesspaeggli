import {
  adminOverviewFeature,
  selectAdminOverview,
  selectAdminOverviewStatus,
} from './admin-overview.feature';
import { initialState } from './admin-overview.state';

describe('adminOverviewFeature', () => {
  it('has the expected initial state', () => {
    expect(adminOverviewFeature.reducer(undefined, { type: '@@init' })).toEqual(initialState);
  });

  it('exposes selectors for the overview state', () => {
    const state = {
      adminOverview: {
        status: 'loaded' as const,
        overview: {
          year: 2026,
          recentDays: 14,
          categories: [{ category: 'catA', provisional: 0, recentProvisional: 0, definitive: 12, toDeliver: 0, qrcode: 0, delivered: 0 }],
        },
      },
    };

    expect(selectAdminOverviewStatus(state)).toBe('loaded');
    expect(selectAdminOverview(state)).toEqual(state.adminOverview.overview);
  });
});
