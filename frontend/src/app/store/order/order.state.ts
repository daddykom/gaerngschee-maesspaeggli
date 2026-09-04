import { ClientOrder } from '../../shared/models/order.model';

export interface OrderState {
  order: ClientOrder | null;
  loading: boolean;
  loaded: boolean;
  errorCode: string | null;
}

export const initialState: OrderState = {
  order: null,
  loading: false,
  loaded: false,
  errorCode: null,
};
