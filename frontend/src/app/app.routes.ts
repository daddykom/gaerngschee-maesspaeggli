import { Routes } from '@angular/router';
import { groupGuard } from './shared/guards/group.guard';

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
    path: 'password-change',
    data: { pageTitle: 'app.passwordChange.pageTitle' },
    loadComponent: () =>
      import('./features/auth/pages/password-change/password-change.component').then(
        (m) => m.PasswordChange,
      ),
  },
  {
    path: 'admin',
    canActivate: [groupGuard(['user', 'admin'])],
    children: [
      {
        path: 'overview',
        data: { pageTitle: 'app.admin.overview.title', pageHeaderLayout: 'wide' },
        loadComponent: () =>
          import('./features/admin/pages/overview/admin-overview.component').then(
            (m) => m.AdminOverviewComponent,
          ),
      },
    ],
  },
];
