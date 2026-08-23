import { createFeature, createReducer, on } from '@ngrx/store';
import { FrontendConfigActions } from './frontend-config.actions';
import { FrontendConfigState, initialState } from './frontend-config.state';

export const frontendConfigFeature = createFeature({
  name: 'frontendConfig',
  reducer: createReducer<FrontendConfigState>(
    initialState,
    on(FrontendConfigActions.load, (state) => ({ ...state, loading: true })),
    on(FrontendConfigActions.loadSuccess, (state, { configs }) => ({ ...state, configs, loading: false })),
    on(FrontendConfigActions.loadFailure, (state) => ({ ...state, loading: false })),
    on(FrontendConfigActions.save, (state) => ({ ...state, saving: true })),
    on(FrontendConfigActions.saveSuccess, (state, { configs }) => ({ ...state, configs, saving: false })),
    on(FrontendConfigActions.saveFailure, (state) => ({ ...state, saving: false })),
  ),
});

export const {
  name: frontendConfigFeatureName,
  reducer: frontendConfigReducer,
  selectConfigs: selectFrontendConfigs,
  selectLoading: selectFrontendConfigLoading,
  selectSaving: selectFrontendConfigSaving,
} = frontendConfigFeature;
