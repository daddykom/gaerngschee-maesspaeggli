export interface AdminOverviewCategory {
  category: string;
  packageCount: number;
}

export interface AdminOverviewSection {
  orderCount: number;
  categories: AdminOverviewCategory[];
}

export interface AdminOverview {
  year: number;
  recentDays: number;
  definitive: AdminOverviewSection;
  provisional: AdminOverviewSection;
  recentProvisional: AdminOverviewSection;
}
