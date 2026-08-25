import { createFeature, createReducer, on } from '@ngrx/store';
import { FairgateTestActions } from './fairgate-test.actions';
import { FairgateTestState, initialState } from './fairgate-test.state';

export const fairgateTestFeature = createFeature({
  name: 'fairgateTest',
  reducer: createReducer<FairgateTestState>(
    initialState,
    on(FairgateTestActions.test, (state) => ({
      ...state,
      loading: true,
      errorCode: null,
    })),
    on(FairgateTestActions.testSuccess, (state, { result }) => ({
      ...state,
      result,
      loading: false,
      errorCode: null,
    })),
    on(FairgateTestActions.testFailure, (state, { errorCode }) => ({
      ...state,
      loading: false,
      errorCode,
    })),
  ),
});

export const {
  name: fairgateTestFeatureName,
  reducer: fairgateTestReducer,
  selectResult: selectFairgateTestResult,
  selectLoading: selectFairgateTestLoading,
  selectErrorCode: selectFairgateTestErrorCode,
} = fairgateTestFeature;
