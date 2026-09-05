import { createActionGroup, emptyProps, props } from '@ngrx/store';
import { ClientOrder, OrderForm } from '../../shared/models/order.model';

export const OrderActions = createActionGroup({
  source: 'Order',
  events: {
    'Order Load Requested': emptyProps(),
    'Order Loaded': props<{ order: ClientOrder | null; form: OrderForm }>(),
    'Order Load Failed': props<{ errorCode: string }>(),
    'Order Form Updated': props<{ form: Partial<OrderForm> }>(),
    'Order Save Requested': emptyProps(),
    'Order Saved': props<{ order: ClientOrder }>(),
    'Order Save Failed': props<{ errorCode: string }>(),
  },
});
