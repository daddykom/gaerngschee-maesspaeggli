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
      error: null,
    })),
    on(AnmeldungActions.submitSuccess, (state, { sent }) => ({
      ...state,
      loading: false,
      sent,
      error: null,
    })),
    on(AnmeldungActions.submitFailure, (state, { error }) => ({
      ...state,
      loading: false,
      sent: false,
      error,
    })),
  ),
});

export const {
  name: anmeldungFeatureName,
  reducer: anmeldungReducer,
  selectAnmeldungState,
  selectLoading: selectAnmeldungLoading,
  selectSent: selectAnmeldungSent,
  selectError: selectAnmeldungError,
} = anmeldungFeature;
