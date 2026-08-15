import { Routes } from '@angular/router';

export const appRoutes: Routes = [
  {
    path: '',
    redirectTo: '/offers/list',
    pathMatch: 'full',
  },
  {
    path: 'offers',
    redirectTo: '/offers/list',
    pathMatch: 'full',
  },
  {
    path: 'offers/list',
    loadComponent: () =>
      import('./features/offers/offer-list/offer-list.container.component').then(
        (m) => m.OfferListContainerComponent,
      ),
  },
];
