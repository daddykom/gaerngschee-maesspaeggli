import { Routes } from '@angular/router';

export const appRoutes: Routes = [
  {
    path: '',
    redirectTo: 'start',
    pathMatch: 'full',
  },
  {
    path: 'start',
    data: { pageTitle: 'app.anmeldung.title', pageHeaderLayout: 'wide' },
    loadComponent: () =>
      import('./features/auth/pages/start/maesspaeggli-start.component').then(
        (m) => m.MaesspaeggliStartComponent,
      ),
  },
  {
    path: 'login',
    data: { pageTitle: 'app.login.pageTitle' },
    loadComponent: () => import('./features/auth/pages/login/login.component').then((m) => m.Login),
  },
  {
    path: 'register',
    loadComponent: () =>
      import('./features/auth/pages/register/register.component').then((m) => m.Register),
  },
  {
    path: 'admin/overview',
    data: { pageTitle: 'app.admin.overview.title' },
    loadComponent: () =>
      import('./features/admin/pages/overview/admin-overview.component').then(
        (m) => m.AdminOverviewComponent,
      ),
  },
];
