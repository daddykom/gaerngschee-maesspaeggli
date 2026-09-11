import { ApplicationConfig, provideBrowserGlobalErrorListeners } from '@angular/core';
import { provideHttpClient, withInterceptors, withXhr } from '@angular/common/http';
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
import { authTokenInterceptor } from './shared/interceptors/auth-token.interceptor';
import { authSessionInterceptor } from './shared/interceptors/auth-session.interceptor';
import { navigationEffects } from './store/navigation/navigation.effects';
import { notificationEffects } from './store/notification/notification.effects';
import { notificationFeature } from './store/notification/notification.feature';
import { frontendConfigEffects } from './store/frontend-config/frontend-config.effects';
import { frontendConfigFeature } from './store/frontend-config/frontend-config.feature';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideHttpClient(withXhr(), withInterceptors([authTokenInterceptor, authSessionInterceptor])),
    provideRouter(appRoutes),
    provideStore(),
    provideState(authFeature),
    provideState(notificationFeature),
    provideState(frontendConfigFeature),
    provideEffects(authEffects, frontendConfigEffects, navigationEffects, notificationEffects),
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
