import { UserGroup } from '../../shared/models/frontend-config.model';

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
  token: null,
  userId: null,
  group: null,
  requiredPasswordReset: false,
  loading: false,
  passwordChangeLoading: false,
  passwordChangeErrorCode: null,
  errorCode: null,
  registrationLoginLoading: false,
  registrationLoginErrorCode: null,
  fairgateUserExists: null,
  childrenCount: null,
  adultsCount: null,
  salutation: null,
};
