import { AdminUser } from '../../shared/services/admin-users.service';

export interface AdminUsersState {
  users: AdminUser[];
  loading: boolean;
  saving: boolean;
  errorCode: string | null;
  success: 'created' | 'updated' | 'deleted' | null;
  emailSentTo: string | null;
}

export const initialState: AdminUsersState = {
  users: [],
  loading: false,
  saving: false,
  errorCode: null,
  success: null,
  emailSentTo: null,
};
