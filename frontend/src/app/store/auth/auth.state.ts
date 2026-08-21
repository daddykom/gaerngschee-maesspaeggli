import { UserGroup } from '../../shared/models/frontend-config.model';

export interface AuthState {
  token: string | null;
  group: UserGroup | null;
  loading: boolean;
  errorCode: string | null;
}

export const initialState: AuthState = {
  token: null,
  group: null,
  loading: false,
  errorCode: null,
};
