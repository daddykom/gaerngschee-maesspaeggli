import { ClientOrder, OrderForm } from '../../shared/models/order.model';

export type OrderState =
  | { status: 'initial' }
  | { status: 'loading' }
  | { status: 'loaded'; order: ClientOrder | null; form: OrderForm }
  | { status: 'error'; errorCode: string };

export const initialState: OrderState = { status: 'initial' };
