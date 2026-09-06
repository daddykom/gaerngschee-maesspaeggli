import { AdminOverview } from '../../shared/models/admin-overview.model';

export type AdminOverviewState =
  | { status: 'initial' }
  | { status: 'loading' }
  | { status: 'loaded'; overview: AdminOverview }
  | { status: 'error'; errorCode: string };

export const initialState: AdminOverviewState = { status: 'initial' };
