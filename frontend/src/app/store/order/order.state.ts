import { ClientOrder, OrderDraft } from '../../shared/models/order.model';

export interface OrderState {
  order: ClientOrder | null;
  draft: OrderDraft | null;
  loading: boolean;
  loaded: boolean;
  saving: boolean;
  errorCode: string | null;
}

export const initialState: OrderState = {
  order: null,
  draft: null,
  loading: false,
  loaded: false,
  saving: false,
  errorCode: null,
};
