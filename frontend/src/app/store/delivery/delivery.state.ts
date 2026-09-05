import { ClientOrder } from '../../shared/models/order.model';

export type DeliveryLoadState =
  | { status: 'initial' }
  | { status: 'loading' }
  | { status: 'loaded' }
  | { status: 'error'; errorCode: string };

export type DeliveryActionState =
  | { status: 'initial' }
  | { status: 'loading' }
  | { status: 'error'; errorCode: string };

export interface DeliveryState {
  form: { email: string };
  search: { email?: string; token?: string } | null;
  load: DeliveryLoadState;
  action: DeliveryActionState;
  order: ClientOrder | null;
  viaToken: boolean;
  clientName: string | null;
  children: Array<{ name: string; birthDate: string | null }>;
}

export const initialState: DeliveryState = {
  form: { email: '' },
  search: null,
  load: { status: 'initial' },
  action: { status: 'initial' },
  order: null,
  viaToken: false,
  clientName: null,
  children: [],
};
