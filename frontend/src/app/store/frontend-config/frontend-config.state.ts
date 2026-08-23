import { FrontendConfig } from '../../shared/models/frontend-config.model';

export interface FrontendConfigState {
  configs: FrontendConfig[];
  loading: boolean;
  saving: boolean;
}

export const initialState: FrontendConfigState = {
  configs: [],
  loading: false,
  saving: false,
};
