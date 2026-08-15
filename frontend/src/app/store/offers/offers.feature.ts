import { createFeature, createReducer, createSelector, on } from '@ngrx/store';
import { OffersActions } from './offers.actions';
import { initialState, OffersState } from './offers.state';

export const offersFeature = createFeature({
  name: 'offers',
  reducer: createReducer<OffersState>(
    initialState,
    on(OffersActions.loadOffers, (state) => ({ ...state, loading: true, error: null })),
    on(OffersActions.loadOffersSuccess, (state, { offers }) => ({
      ...state,
      offers,
      loading: false,
    })),
    on(OffersActions.loadOffersFailure, (state, { error }) => ({
      ...state,
      loading: false,
      error,
    })),
  ),
});

export const {
  name: offersFeatureName,
  reducer: offersReducer,
  selectOffersState,
  selectOffers,
  selectSelectedOffer,
} = offersFeature;

export const selectOffersLoading = createSelector(
  selectOffersState,
  (state) => state.loading,
);

export const selectCurrentPosition = createSelector(
  selectOffersState,
  (state) => state.currentPosition,
);
