export type OrderCategory = 'catA' | 'catB' | 'catC' | 'catD' | 'catE' | 'catF' | 'catG';
export type OrderPersonType = 'adult' | 'child';

export interface OrderForm {
  adultsCount: number;
  childrenCount: number;
  adults: (OrderCategory | '')[];
  children: (OrderCategory | '')[];
}

export interface OrderItem {
  personType: OrderPersonType;
  category: OrderCategory;
  quantity: number;
}

export interface ClientOrder {
  id: string;
  userId: string;
  year: number;
  status: 'provisional' | 'definitive';
  adultsCount: number;
  childrenCount: number;
  items: OrderItem[];
  createdAt: string | null;
  updatedAt: string | null;
}

export interface ClientOrderResponse {
  order: ClientOrder | null;
}
