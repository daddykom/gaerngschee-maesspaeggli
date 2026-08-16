import { Routes } from '@angular/router';

export const appRoutes: Routes = [
  {
    path: '',
    redirectTo: 'anmeldung',
    pathMatch: 'full',
  },
  {
    path: 'anmeldung',
    loadComponent: () =>
      import('../../features/auth/pages/start/start.component').then((m) => m.StartComponent),
  },
  {
    path: 'login',
    loadComponent: () =>
      import('../../features/auth/pages/login/login.component').then((m) => m.Login),
  },
  {
    path: 'register',
    loadComponent: () =>
      import('../../features/auth/pages/register/register.component').then((m) => m.Register),
  },
];
