import { ClientOrder } from './order.model';

export interface DeliveryOrderResponse {
  order: ClientOrder;
  viaToken: boolean;
  clientName: string | null;
  children: Array<{ name: string; birthDate: string | null }>;
}
