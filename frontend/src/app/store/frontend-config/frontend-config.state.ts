import { FrontendConfig, PublicFrontendConfig } from '../../shared/models/frontend-config.model';

export interface FrontendConfigState {
  configs: FrontendConfig[];
  publicConfigs: PublicFrontendConfig[];
  loading: boolean;
  saving: boolean;
}

export const initialState: FrontendConfigState = {
  configs: [],
  publicConfigs: [],
  loading: false,
  saving: false,
};
