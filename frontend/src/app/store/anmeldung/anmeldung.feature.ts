import { createFeature, createReducer, on } from '@ngrx/store';
import { AnmeldungActions } from './anmeldung.actions';
import { initialState, AnmeldungState } from './anmeldung.state';

export const anmeldungFeature = createFeature({
  name: 'anmeldung',
  reducer: createReducer<AnmeldungState>(
    initialState,
    on(AnmeldungActions.submit, (state) => ({
      ...state,
      loading: true,
      sent: null,
      sendError: false,
    })),
    on(AnmeldungActions.submitSuccess, (state, { sent }) => ({
      ...state,
      loading: false,
      sent,
      sendError: false,
    })),
    on(AnmeldungActions.submitFailure, (state) => ({
      ...state,
      loading: false,
      sent: false,
      sendError: true,
    })),
  ),
});

export const {
  name: anmeldungFeatureName,
  reducer: anmeldungReducer,
  selectAnmeldungState,
  selectLoading: selectAnmeldungLoading,
  selectSent: selectAnmeldungSent,
  selectSendError: selectAnmeldungSendError,
} = anmeldungFeature;
