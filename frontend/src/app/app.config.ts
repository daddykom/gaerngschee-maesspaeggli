import { ApplicationConfig, provideBrowserGlobalErrorListeners } from '@angular/core';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { provideRouter } from '@angular/router';
import { provideStore } from '@ngrx/store';
import { provideEffects } from '@ngrx/effects';
import { provideState } from '@ngrx/store';
import { provideStoreDevtools } from '@ngrx/store-devtools';
import { provideAnimations } from '@angular/platform-browser/animations';
import { provideTranslateService } from '@ngx-translate/core';
import { provideTranslateHttpLoader } from '@ngx-translate/http-loader';
import { appRoutes } from './app.routes';
import { authEffects } from './store/auth/auth.effects';
import { authFeature } from './store/auth/auth.feature';
import { startEffects } from './store/start/start.effects';
import { startFeature } from './store/start/start.feature';
import { adminOverviewFeature } from './store/admin-overview/admin-overview.feature';
import { adminUsersEffects } from './store/admin-users/admin-users.effects';
import { adminUsersFeature } from './store/admin-users/admin-users.feature';
import { authTokenInterceptor } from './shared/interceptors/auth-token.interceptor';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideHttpClient(withInterceptors([authTokenInterceptor])),
    provideRouter(appRoutes),
    provideStore(),
    provideState(startFeature),
    provideState(authFeature),
    provideState(adminOverviewFeature),
    provideState(adminUsersFeature),
    provideEffects(startEffects, authEffects, adminUsersEffects),
    provideStoreDevtools(),
    provideAnimations(),
    provideTranslateService({
      fallbackLang: 'de',
      lang: 'de',
      loader: provideTranslateHttpLoader({
        prefix: './i18n/',
        suffix: '.json',
      }),
    }),
  ],
};
