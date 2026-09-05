import { ClientOrder } from './order.model';

export interface DeliveryOrderResponse {
  order: ClientOrder;
  viaToken: boolean;
}
