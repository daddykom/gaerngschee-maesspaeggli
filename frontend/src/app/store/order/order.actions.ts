import { createActionGroup, emptyProps, props } from '@ngrx/store';
import { ClientOrder, OrderDraft } from '../../shared/models/order.model';

export const OrderActions = createActionGroup({
  source: 'Order',
  events: {
    'Load Current': emptyProps(),
    'Load Current Success': props<{ order: ClientOrder | null }>(),
    'Load Current Failure': props<{ errorCode: string }>(),
    'Set Draft': props<{ draft: OrderDraft }>(),
    Save: props<{ draft: OrderDraft }>(),
    'Save Success': props<{ order: ClientOrder }>(),
    'Save Failure': props<{ errorCode: string }>(),
  },
});
