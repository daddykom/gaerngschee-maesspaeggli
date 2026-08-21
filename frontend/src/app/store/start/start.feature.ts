import { createFeature, createReducer, on } from '@ngrx/store';
import { StartActions } from './start.actions';
import { initialState, StartState } from './start.state';

export const startFeature = createFeature({
  name: 'start',
  reducer: createReducer<StartState>(
    initialState,
    on(StartActions.submit, (state) => ({
      ...state,
      loading: true,
      sent: null,
      sendError: false,
    })),
    on(StartActions.submitSuccess, (state, { sent }) => ({
      ...state,
      loading: false,
      sent,
      sendError: false,
    })),
    on(StartActions.submitFailure, (state) => ({
      ...state,
      loading: false,
      sent: false,
      sendError: true,
    })),
  ),
});

export const {
  name: startFeatureName,
  reducer: startReducer,
  selectStartState,
  selectLoading: selectStartLoading,
  selectSent: selectStartSent,
  selectSendError: selectStartSendError,
} = startFeature;
