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
      import('../../features/start/start.component').then((m) => m.StartComponent),
  },
  {
    path: 'login',
    loadComponent: () =>
      import('../../features/auth/components/login/login.component').then((m) => m.Login),
  },
  {
    path: 'register',
    loadComponent: () =>
      import('../../features/auth/components/register/register.component').then((m) => m.Register),
  },
];
