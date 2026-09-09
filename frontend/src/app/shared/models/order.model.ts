export const adultCategories = ['catA', 'catB'] as const;
export type AdultCategory = (typeof adultCategories)[number];

export const childCategories = ['catC', 'catD', 'catE', 'catF', 'catG'] as const;
export type ChildCategory = (typeof childCategories)[number];

export type OrderCategory = AdultCategory | ChildCategory;
export const orderCategories = [...adultCategories, ...childCategories] as const;
export type CategorySelection = OrderCategory | '';
export type OrderPersonType = 'adult' | 'child';

export interface OrderForm {
  adultsCount: number;
  childrenCount: number;
  adults: CategorySelection[];
  children: CategorySelection[];
}

export interface OrderItem {
  personType: OrderPersonType;
  category: OrderCategory;
  quantity: number;
}

export type OrderStatus = 'provisional' | 'definitive' | 'toDeliver' | 'qrcode' | 'delivered';

export interface ClientOrder {
  id: string;
  userId: string;
  year: number;
  status: OrderStatus;
  adultsCount: number;
  childrenCount: number;
  items: OrderItem[];
  createdAt: string | null;
  updatedAt: string | null;
}

export interface ClientOrderResponse {
  order: ClientOrder | null;
}
