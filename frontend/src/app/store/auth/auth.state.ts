import { UserGroup } from '../../shared/models/frontend-config.model';

export interface AuthState {
  token: string | null;
  userId: string | null;
  group: UserGroup | null;
  requiredPasswordReset: boolean;
  loading: boolean;
  errorCode: string | null;
}

export const initialState: AuthState = {
  token: null,
  userId: null,
  group: null,
  requiredPasswordReset: false,
  loading: false,
  errorCode: null,
};
