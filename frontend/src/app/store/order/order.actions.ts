import { createActionGroup, emptyProps, props } from '@ngrx/store';
import { ClientOrder } from '../../shared/models/order.model';

export const OrderActions = createActionGroup({
  source: 'Order',
  events: {
    'Load Current': emptyProps(),
    'Load Current Success': props<{ order: ClientOrder | null }>(),
    'Load Current Failure': props<{ errorCode: string }>(),
  },
});
