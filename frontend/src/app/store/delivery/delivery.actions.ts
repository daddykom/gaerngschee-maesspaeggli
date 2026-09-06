import { createActionGroup, props } from '@ngrx/store';
import { DeliveryOrderResponse } from '../../shared/models/delivery.model';

export const DeliveryActions = createActionGroup({
  source: 'Delivery',
  events: {
    'Email Changed': props<{ email: string }>(),
    'Load Requested': props<{ email?: string; token?: string }>(),
    'Load Success': props<DeliveryOrderResponse>(),
    'Load Failure': props<{ errorCode: string }>(),
    'Status Change Requested': props<{ orderId: string; transition: 'deliver' | 'undo' }>(),
    'Status Change Success': props<{ status: 'qrcode' | 'delivered' }>(),
    'Status Change Failure': props<{ errorCode: string }>(),
  },
});
