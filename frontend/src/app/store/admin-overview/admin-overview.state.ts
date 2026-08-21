export interface Kategory {
  kategoryId: string;
  numbPackages: number;
}

export interface AdminOverviewState {
  numbOrders: number;
  kategories: Kategory[];
}

export const initialState: AdminOverviewState = {
  numbOrders: 253,
  kategories: [
    { kategoryId: 'A', numbPackages: 120 },
    { kategoryId: 'B', numbPackages: 154 },
    { kategoryId: 'C', numbPackages: 123 },
  ],
};
