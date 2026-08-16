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
      import('./features/anmeldung/anmeldung.component').then((m) => m.AnmeldungComponent),
  },
  {
    path: 'login',
    loadComponent: () =>
      import('../../features/auth/components/login/login').then((m) => m.Login),
  },
  {
    path: 'register',
    loadComponent: () =>
      import('../../features/auth/components/register/register').then((m) => m.Register),
  },
];
