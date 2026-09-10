import { FrontendConfig, PublicFrontendConfig } from '../../shared/models/frontend-config.model';

export type PublicConfigStatus = 'initial' | 'loading' | 'loaded' | 'error';

export interface FrontendConfigState {
  configs: FrontendConfig[];
  publicConfigs: PublicFrontendConfig[];
  loading: boolean;
  saving: boolean;
  publicStatus: PublicConfigStatus;
  publicErrorCode: string | null;
}

export const initialState: FrontendConfigState = {
  configs: [],
  publicConfigs: [],
  loading: false,
  saving: false,
  publicStatus: 'initial',
  publicErrorCode: null,
};
