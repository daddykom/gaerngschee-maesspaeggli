import { AdminUser, ClientDeletionLookup } from '../../shared/services/admin-users.service';

export type ClientDeletionLookupState =
  | { status: 'initial'; email: string }
  | { status: 'loading'; email: string }
  | { status: 'loaded'; email: string; result: ClientDeletionLookup }
  | { status: 'error'; email: string; errorCode: string };

export interface AdminUsersState {
  users: AdminUser[];
  loading: boolean;
  saving: boolean;
  deletingClientOrder: boolean;
  clientDeletion: ClientDeletionLookupState;
}

export const initialState: AdminUsersState = {
  users: [],
  loading: false,
  saving: false,
  deletingClientOrder: false,
  clientDeletion: { status: 'initial', email: '' },
};
