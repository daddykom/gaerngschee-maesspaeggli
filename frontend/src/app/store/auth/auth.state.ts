import { UserGroup } from '../../shared/models/frontend-config.model';
import { loadPersistedAuthState } from '../../shared/services/auth-storage';

const persistedAuth = loadPersistedAuthState();

export interface AuthState {
  token: string | null;
  userId: string | null;
  group: UserGroup | null;
  requiredPasswordReset: boolean;
  loading: boolean;
  passwordChangeLoading: boolean;
  passwordChangeErrorCode: string | null;
  errorCode: string | null;
  registrationLoginLoading: boolean;
  registrationLoginErrorCode: string | null;
  fairgateUserExists: boolean | null;
  childrenCount: number | null;
  adultsCount: number | null;
  salutation: string | null;
}

export const initialState: AuthState = {
  token: persistedAuth.token ?? null,
  userId: persistedAuth.userId ?? null,
  group: persistedAuth.group ?? null,
  requiredPasswordReset: false,
  loading: false,
  passwordChangeLoading: false,
  passwordChangeErrorCode: null,
  errorCode: null,
  registrationLoginLoading: false,
  registrationLoginErrorCode: null,
  fairgateUserExists: persistedAuth.fairgateUserExists ?? null,
  childrenCount: persistedAuth.childrenCount ?? null,
  adultsCount: persistedAuth.adultsCount ?? null,
  salutation: persistedAuth.salutation ?? null,
};
