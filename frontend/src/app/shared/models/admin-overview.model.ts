export interface AdminOverviewCategory {
  category: string;
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
  categories: AdminOverviewCategory[];
}
