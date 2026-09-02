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
    data: { pageTitle: 'app.anmeldung.title' },
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
    path: 'client-login',
    data: { pageTitle: 'app.clientLogin.pageTitle' },
    loadComponent: () =>
      import('./features/auth/pages/client-login/client-login.component').then((m) => m.ClientLoginComponent),
  },
  {
    path: 'order',
    canActivate: [groupGuard(['client'])],
    data: { pageTitle: 'app.order.pageTitle' },
    loadComponent: () => import('./features/order/pages/order/order.component').then((m) => m.OrderComponent),
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
    path: 'not-found',
    data: { pageTitle: 'app.notFound.pageTitle' },
    loadComponent: () =>
      import('./features/errors/pages/not-found/not-found.component').then(
        (m) => m.NotFoundComponent,
      ),
  },
  {
    path: 'admin',
    canActivate: [groupGuard(['user', 'admin'])],
    children: [
      {
        path: 'overview',
        data: { pageTitle: 'app.admin.overview.title' },
        loadComponent: () =>
          import('./features/admin/pages/overview/admin-overview.component').then(
            (m) => m.AdminOverviewComponent,
          ),
      },
      {
        path: 'fairgate-test',
        canActivate: [groupGuard(['admin'])],
        data: { pageTitle: 'app.admin.fairgateTest.title' },
        loadComponent: () =>
          import('./features/admin/pages/fairgate-test/fairgate-test.component').then(
            (m) => m.FairgateTestComponent,
          ),
      },
      {
        path: 'configuration',
        data: { pageTitle: 'app.admin.configuration.title' },
        loadComponent: () =>
          import('./features/admin/pages/configuration/configuration.component').then(
            (m) => m.ConfigurationComponent,
          ),
      },
      {
        path: 'users',
        children: [
          {
            path: 'new',
            canActivate: [groupGuard(['admin'])],
            data: { pageTitle: 'app.admin.users.createTitle' },
            loadComponent: () =>
              import('./features/admin/pages/users/user-edit.component').then((m) => m.UserEditComponent),
          },
          {
            path: ':userId',
            canActivate: [groupGuard(['user', 'admin'])],
            data: { pageTitle: 'app.admin.users.editTitle' },
            loadComponent: () =>
              import('./features/admin/pages/users/user-edit.component').then((m) => m.UserEditComponent),
          },
          {
            path: '',
            canActivate: [groupGuard(['admin'])],
            data: { pageTitle: 'app.admin.users.title' },
            loadComponent: () =>
              import('./features/admin/pages/users/users.component').then((m) => m.UsersComponent),
          },
        ],
      },
    ],
  },
  {
    path: '**',
    redirectTo: 'not-found',
  },
];
