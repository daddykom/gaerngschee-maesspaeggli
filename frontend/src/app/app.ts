import { ChangeDetectionStrategy, Component, computed, inject } from '@angular/core';
import { ActivatedRoute, NavigationEnd, Router, RouterLink, RouterOutlet } from '@angular/router';
import { Store } from '@ngrx/store';
import { TranslatePipe } from '@ngx-translate/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { filter, startWith } from 'rxjs';
import { InfoBoxComponent } from './shared/components/info-box/info-box';
import { AuthActions } from './store/auth/auth.actions';
import { selectAuthGroup } from './store/auth/auth.feature';
import { SessionActions } from './store/auth/session.actions';
import { selectNotification } from './store/notification/notification.feature';
import { selectFrontendPublicConfigStatus } from './store/frontend-config/frontend-config.feature';
import { AdminNavigationComponent } from './shared/components/admin-navigation/admin-navigation';

@Component({
  imports: [
    RouterOutlet,
    RouterLink,
    AdminNavigationComponent,
    TranslatePipe,
    InfoBoxComponent,
  ],
  selector: 'app-root',
  templateUrl: './app.html',
  changeDetection: ChangeDetectionStrategy.Eager,
  styleUrl: './app.scss',
})
export class App {
  protected title = 'Gaerngschee';
  private readonly store = inject(Store);
  private readonly router = inject(Router);
  private readonly activatedRoute = inject(ActivatedRoute);

  private readonly navigation = toSignal(
    this.router.events.pipe(
      filter((event) => event instanceof NavigationEnd),
      startWith(null),
    ),
    { initialValue: null },
  );

  readonly notification = this.store.selectSignal(selectNotification);
  readonly publicConfigStatus = this.store.selectSignal(selectFrontendPublicConfigStatus);
  readonly authGroup = this.store.selectSignal(selectAuthGroup);
  readonly isAdmin = computed(() => this.authGroup() === 'admin');

  constructor() {
    if (this.store.selectSignal(selectAuthGroup)() !== null) {
      this.store.dispatch(SessionActions.pollingStarted());
    }
  }

  logout(): void {
    this.store.dispatch(AuthActions.logoutRequested({ redirectTo: '/login' }));
  }

  readonly pageTitleKey = computed(() => {
    this.navigation();

    let route = this.activatedRoute;
    while (route.firstChild) {
      route = route.firstChild;
    }

    return route.snapshot.data['pageTitle'] as string | undefined;
  });

}
