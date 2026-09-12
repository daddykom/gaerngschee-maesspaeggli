import { Routes } from '@angular/router';
import { provideEffects } from '@ngrx/effects';
import { provideState } from '@ngrx/store';
import { groupGuard } from './shared/guards/group.guard';
import { orderResolver } from './features/order/order.resolver';
import { clientLoginGuard } from './features/auth/guards/client-login.guard';
import { adminOverviewResolver } from './features/admin/pages/overview/admin-overview.resolver';
import { configurationResolver } from './features/admin/pages/configuration/configuration.resolver';
import { publicConfigurationResolver } from './features/public-configuration/public-configuration.resolver';
import { startEffects } from './store/start/start.effects';
import { startFeature } from './store/start/start.feature';
import { orderEffects } from './store/order/order.effects';
import { orderFeature } from './store/order/order.feature';
import { deliveryEffects } from './store/delivery/delivery.effects';
import { deliveryFeature } from './store/delivery/delivery.feature';
import { passwordResetFeature } from './store/password-reset/password-reset.feature';
import { adminOverviewFeature } from './store/admin-overview/admin-overview.feature';
import { adminOverviewEffects } from './store/admin-overview/admin-overview.effects';
import { adminUsersEffects } from './store/admin-users/admin-users.effects';
import { adminUsersFeature } from './store/admin-users/admin-users.feature';
import { fairgateTestEffects } from './store/fairgate-test/fairgate-test.effects';
import { fairgateTestFeature } from './store/fairgate-test/fairgate-test.feature';

export const appRoutes: Routes = [
  {
    path: '',
    children: [
      {
        path: '',
        pathMatch: 'full',
        data: { pageTitle: 'app.home.title' },
        loadComponent: () => import('./features/home/pages/home/home.component').then((m) => m.HomeComponent),
      },
      {
        path: 'start',
        providers: [provideState(startFeature), provideEffects(startEffects)],
        data: { pageTitle: 'app.anmeldung.title' },
        loadComponent: () => import('./features/auth/pages/start/maesspaeggli-start.component').then((m) => m.MaesspaeggliStartComponent),
      },
      {
        path: 'login',
        data: { pageTitle: 'app.login.pageTitle' },
        loadComponent: () => import('./features/auth/pages/login/login.component').then((m) => m.Login),
      },
      {
        path: 'client-login',
        canMatch: [clientLoginGuard],
        children: [{ path: '', redirectTo: 'start', pathMatch: 'full' }],
      },
      {
        path: 'order',
        canActivate: [groupGuard(['client'])],
        resolve: { order: orderResolver },
        providers: [provideState(orderFeature), provideEffects(orderEffects)],
        data: { pageTitle: 'app.order.pageTitle' },
        children: [
          { path: '', redirectTo: 'edit', pathMatch: 'full' },
          { path: 'edit', data: { pageTitle: 'app.order.pageTitle' }, loadComponent: () => import('./features/order/pages/order/order.component').then((m) => m.OrderComponent) },
          { path: 'summary', data: { pageTitle: 'app.order.summary.pageTitle' }, loadComponent: () => import('./features/order/pages/order-summary/order-summary.component').then((m) => m.OrderSummaryComponent) },
        ],
      },
      {
        path: 'password-change',
        data: { pageTitle: 'app.passwordChange.pageTitle' },
        loadComponent: () => import('./features/auth/pages/password-change/password-change.component').then((m) => m.PasswordChange),
      },
      {
        path: 'password-reset-request',
        providers: [provideState(passwordResetFeature)],
        data: { pageTitle: 'app.passwordResetRequest.pageTitle' },
        loadComponent: () => import('./features/auth/pages/password-reset-request/password-reset-request.component').then((m) => m.PasswordResetRequestComponent),
      },
      {
        path: 'password-reset',
        providers: [provideState(passwordResetFeature)],
        data: { pageTitle: 'app.passwordReset.pageTitle' },
        loadComponent: () => import('./features/auth/pages/password-reset/password-reset.component').then((m) => m.PasswordResetComponent),
      },
      { path: 'not-found', data: { pageTitle: 'app.notFound.pageTitle' }, loadComponent: () => import('./features/errors/pages/not-found/not-found.component').then((m) => m.NotFoundComponent) },
      { path: 'delivery', canActivate: [groupGuard(['user', 'admin'])], providers: [provideState(deliveryFeature), provideEffects(deliveryEffects)], data: { pageTitle: 'app.delivery.title' }, loadComponent: () => import('./features/delivery/pages/delivery').then((m) => m.Delivery) },
      {
        path: 'admin', canActivate: [groupGuard(['user', 'admin'])], providers: [
          provideState(adminOverviewFeature),
          provideState(adminUsersFeature),
          provideState(fairgateTestFeature),
          provideEffects(adminOverviewEffects, adminUsersEffects, fairgateTestEffects),
        ], children: [
          { path: 'overview', resolve: { overview: adminOverviewResolver }, data: { pageTitle: 'app.admin.overview.title' }, loadComponent: () => import('./features/admin/pages/overview/admin-overview.component').then((m) => m.AdminOverviewComponent) },
          { path: 'fairgate-test', canActivate: [groupGuard(['admin'])], data: { pageTitle: 'app.admin.fairgateTest.title' }, loadComponent: () => import('./features/admin/pages/fairgate-test/fairgate-test.component').then((m) => m.FairgateTestComponent) },
          { path: 'configuration', resolve: { configuration: configurationResolver }, data: { pageTitle: 'app.admin.configuration.title' }, loadComponent: () => import('./features/admin/pages/configuration/configuration.component').then((m) => m.ConfigurationComponent) },
          { path: 'users', children: [
            { path: 'new', canActivate: [groupGuard(['admin'])], data: { pageTitle: 'app.admin.users.createTitle' }, loadComponent: () => import('./features/admin/pages/users/user-edit.component').then((m) => m.UserEditComponent) },
            { path: ':userId', canActivate: [groupGuard(['user', 'admin'])], data: { pageTitle: 'app.admin.users.editTitle' }, loadComponent: () => import('./features/admin/pages/users/user-edit.component').then((m) => m.UserEditComponent) },
            { path: '', canActivate: [groupGuard(['admin'])], data: { pageTitle: 'app.admin.users.title' }, loadComponent: () => import('./features/admin/pages/users/users.component').then((m) => m.UsersComponent) },
          ] },
        ],
      },
      { path: '**', redirectTo: 'not-found' },
    ],
    resolve: { publicConfiguration: publicConfigurationResolver },
  },
];
