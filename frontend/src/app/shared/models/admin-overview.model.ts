export interface AdminOverviewCategory {
  category: string;
  provisional: number;
  recentProvisional: number;
  definitive: number;
  toDeliver: number;
  qrcode: number;
  delivered: number;
}

export interface AdminOverviewOrders {
  provisional: number;
  recentProvisional: number;
  definitive: number;
  toDeliver: number;
  qrcode: number;
  delivered: number;
}

export interface AdminOverview {
  year: number;
  recentDays: number;
  orders: AdminOverviewOrders;
  categories: AdminOverviewCategory[];
}
